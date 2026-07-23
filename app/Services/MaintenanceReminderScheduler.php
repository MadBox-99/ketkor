<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MaintenanceReminderStage;
use App\Models\MaintenanceReminderSetting;
use App\Models\Product;
use App\Models\User;
use App\Support\MaintenanceSchedule;
use App\Support\PendingMaintenanceReminder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MaintenanceReminderScheduler
{
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
}
