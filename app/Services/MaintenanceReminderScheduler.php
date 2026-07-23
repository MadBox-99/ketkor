<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MaintenanceReminderStage;
use App\Enums\MaintenanceReminderStatus;
use App\Mail\MaintenanceReminderMail;
use App\Models\MaintenanceReminder;
use App\Models\MaintenanceReminderSetting;
use App\Models\Product;
use App\Models\User;
use App\Support\MaintenanceReminderTemplateRenderer;
use App\Support\MaintenanceSchedule;
use App\Support\PendingMaintenanceReminder;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MaintenanceReminderScheduler
{
    public function __construct(
        private MaintenanceReminderTemplateRenderer $renderer,
    ) {}

    /**
     * Az adott napra esedékes emlékeztetők, készülék × címzett bontásban.
     *
     * @return Collection<int, PendingMaintenanceReminder>
     */
    public function pendingFor(CarbonImmutable $day): Collection
    {
        $settings = MaintenanceReminderSetting::current();

        if (! $settings->enabled) {
            return collect();
        }

        $day = $day->startOfDay();

        return Product::query()
            ->where('maintenance_reminders_enabled', true)
            ->whereNotNull('warrantee_date')
            ->whereDate('warrantee_date', '>=', $day->toDateString())
            ->get()
            ->flatMap(function (Product $product) use ($day, $settings): Collection {
                $schedule = MaintenanceSchedule::for($product);

                if ($schedule === null) {
                    return collect();
                }

                $stage = $this->resolveStage($schedule, $day, $settings);

                if ($stage === null) {
                    return collect();
                }

                return $this->recipientsFor($product)->map(
                    fn (User $user): PendingMaintenanceReminder => new PendingMaintenanceReminder(
                        product: $product,
                        user: $user,
                        stage: $stage['stage'],
                        stageKey: $stage['stage_key'],
                        schedule: $schedule,
                    ),
                );
            })
            ->values();
    }

    /**
     * A készülék értesíthető címzettjei, duplikátum nélkül.
     *
     * @return Collection<int, User>
     */
    public function recipientsFor(Product $product): Collection
    {
        return $product->users
            ->when(
                $product->user_id !== null,
                fn (Collection $users): Collection => $users->concat(
                    User::query()->whereKey($product->user_id)->get(),
                ),
            )
            ->unique('id')
            ->filter(fn (User $user): bool => $user->maintenance_reminders_enabled
                && filled($user->email))
            ->values();
    }

    /**
     * A készülék önmagában jogosult-e emlékeztetőre, a naptól függetlenül.
     */
    public function isProductEligible(Product $product, CarbonImmutable $day): bool
    {
        return MaintenanceReminderSetting::current()->enabled
            && $product->maintenance_reminders_enabled
            && $product->warrantee_date !== null
            && CarbonImmutable::parse($product->warrantee_date)->startOfDay()->greaterThanOrEqualTo($day->startOfDay());
    }

    /**
     * Melyik emlékeztető szakasz esik erre a napra, ha esik egyáltalán.
     *
     * @return array{stage: MaintenanceReminderStage, stage_key: int}|null
     */
    public function resolveStage(
        MaintenanceSchedule $schedule,
        CarbonImmutable $day,
        ?MaintenanceReminderSetting $settings = null,
    ): ?array {
        $settings ??= MaintenanceReminderSetting::current();
        $day = $day->startOfDay();
        $due = $schedule->dueDate->startOfDay();

        foreach ($settings->advance_days as $days) {
            if ($day->equalTo($due->subDays((int) $days))) {
                return ['stage' => MaintenanceReminderStage::Advance, 'stage_key' => (int) $days];
            }
        }

        if ($day->greaterThan($due)) {
            $elapsed = (int) $due->diffInDays($day);
            $occurrence = intdiv($elapsed, $settings->overdue_repeat_days);

            if ($occurrence >= 1 && $occurrence <= $settings->overdue_max_count) {
                return ['stage' => MaintenanceReminderStage::Overdue, 'stage_key' => $occurrence];
            }
        }

        return null;
    }

    /**
     * Kiküldi az adott napra esedékes összes emlékeztetőt, és visszaadja a sikeres levelek számát.
     */
    public function run(CarbonImmutable $day): int
    {
        return $this->pendingFor($day)
            ->filter(fn (PendingMaintenanceReminder $reminder): bool => $this->send($reminder)?->status === MaintenanceReminderStatus::Sent)
            ->count();
    }

    /**
     * Egy készülék összes címzettjének azonnali emlékeztetőt küld, a naptári feltételtől függetlenül.
     */
    public function sendManually(Product $product): int
    {
        $day = CarbonImmutable::now()->startOfDay();

        if (! $this->isProductEligible($product, $day)) {
            return 0;
        }

        $schedule = MaintenanceSchedule::for($product);

        if ($schedule === null) {
            return 0;
        }

        return $this->recipientsFor($product)
            ->filter(function (User $user) use ($product, $schedule): bool {
                $reminder = new PendingMaintenanceReminder(
                    product: $product,
                    user: $user,
                    stage: MaintenanceReminderStage::Manual,
                    stageKey: $this->nextManualStageKey($product, $user, $schedule),
                    schedule: $schedule,
                );

                return $this->send($reminder)?->status === MaintenanceReminderStatus::Sent;
            })
            ->count();
    }

    /**
     * Kiküld egy emlékeztetőt és naplózza az eredményt.
     *
     * Az emlékeztetőt lefoglal az adatbázisban a levél kiküldése előtt, így egy párhuzamos futás
     * nem tudja ugyanaz az új emlékeztetőt kétszer elküldeni (az egyedi index ezt kényszeríti).
     * Egy már `Pending` vagy `Failed` állapotban lévő sor helyén újra próbálkozik, de ez a
     * visszapróbálkozási útvonal nem kizárólagos: két egyidejű ütemezőfutás mindkettő elküldheti.
     * A napi parancs `withoutOverlapping()` alatt fut, ez gyakorlatban megakadályozza ezt.
     *
     * Ha a levél kiküldése után a végső mentés hibába ütközik, a levél már kiment, de a sor
     * `Pending` állapotban marad, így egy további futás újra megpróbálja elküldeni — ezt szándékosan
     * vállaljuk, mivel az egész köteg leállítása rosszabb volna.
     *
     * Null-t ad vissza, ha az emlékeztetőt korábban már sikeresen elküldte, vagy ha egy párhuzamos
     * futás közben egy új emlékeztetőt már lefoglalt.
     */
    public function send(PendingMaintenanceReminder $reminder): ?MaintenanceReminder
    {
        $keys = [
            'product_id' => $reminder->product->getKey(),
            'user_id' => $reminder->user->getKey(),
            'due_date' => $reminder->schedule->dueDate->toDateString(),
            'stage' => $reminder->stage,
            'stage_key' => $reminder->stageKey,
        ];

        $log = DB::transaction(function () use ($reminder, $keys): ?MaintenanceReminder {
            $existing = MaintenanceReminder::query()
                ->where($keys)
                ->lockForUpdate()
                ->first();

            if ($existing !== null && $existing->status === MaintenanceReminderStatus::Sent) {
                return null;
            }

            $log = $existing ?? new MaintenanceReminder($keys);

            $log->fill([
                'email' => $reminder->user->email,
                'last_maintenance_at' => $reminder->schedule->fromMaintenanceLog
                    ? $reminder->schedule->baseDate->toDateString()
                    : null,
                'status' => MaintenanceReminderStatus::Pending,
                'sent_at' => null,
                'error' => null,
            ]);

            try {
                $log->save();
            } catch (QueryException $exception) {
                /** Egy párhuzamos futás már lefoglalta ezt az emlékeztetőt. */
                return null;
            }

            return $log;
        });

        if ($log === null) {
            return null;
        }

        $settings = MaintenanceReminderSetting::current();
        $rendered = $this->renderer->render($reminder, $settings);

        try {
            Mail::to($reminder->user->email)->send(new MaintenanceReminderMail(
                product: $reminder->product,
                subjectLine: $rendered['subject'],
                body: $rendered['body'],
                bookingUrl: $settings->booking_url,
                contactPhone: $settings->contact_phone,
                contactEmail: $settings->contact_email,
            ));

            $log->fill([
                'status' => MaintenanceReminderStatus::Sent,
                'sent_at' => CarbonImmutable::now(),
                'error' => null,
            ]);
        } catch (Throwable $exception) {
            $log->fill([
                'status' => MaintenanceReminderStatus::Failed,
                'sent_at' => null,
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            $log->save();
        } catch (Throwable $exception) {
            Log::error('Nem sikerült elmenteni az emlékeztető állapotát a levél kiküldése után.', [
                'product_id' => $reminder->product->getKey(),
                'user_id' => $reminder->user->getKey(),
                'exception' => $exception->getMessage(),
            ]);
        }

        return $log;
    }

    /**
     * A következő szabad sorszám a manuális küldésekhez, hogy az unique index ne ütközzön.
     */
    private function nextManualStageKey(Product $product, User $user, MaintenanceSchedule $schedule): int
    {
        return MaintenanceReminder::query()
            ->where('product_id', $product->getKey())
            ->where('user_id', $user->getKey())
            ->where('due_date', $schedule->dueDate->toDateString())
            ->where('stage', MaintenanceReminderStage::Manual)
            ->max('stage_key') + 1;
    }
}
