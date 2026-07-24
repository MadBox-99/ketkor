<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MaintenanceReminderScheduler;
use App\Support\PendingMaintenanceReminder;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

#[Description('Karbantartás emlékeztető e-mailek kiküldése')]
#[Signature('maintenance:send-reminders
                            {--dry-run : Csak kiírja, mi menne ki, nem küld és nem naplóz}
                            {--catch-up=1 : Ennyi korábbi napot is ellenőriz visszamenőleg}')]
class SendMaintenanceRemindersCommand extends Command implements Isolatable
{
    /**
     * Alapértelmezetten elszigetelt: egy kézi futtatás sem futhat a 08:00-as ütemezett futással
     * párhuzamosan. Az `--isolated` explicit megadása nélkül is érvényes, mert alapból be van
     * kapcsolva. A `Schedule::command()` `withoutOverlapping()`-ja ettől függetlenül megmarad, mert
     * két ütemezett futás közötti átfedést önmagában kezeli.
     */
    protected $isolated = true;

    public function handle(MaintenanceReminderScheduler $scheduler): int
    {
        $catchUp = max(0, (int) $this->option('catch-up'));
        $today = CarbonImmutable::now()->startOfDay();
        $sent = 0;

        for ($offset = $catchUp; $offset >= 0; $offset--) {
            $day = $today->subDays($offset);

            if ($this->option('dry-run')) {
                $scheduler->pendingFor($day)->each(
                    fn (PendingMaintenanceReminder $reminder) => $this->line(sprintf(
                        '%s | %s | %s | %s (%d)',
                        $day->toDateString(),
                        $reminder->product->serial_number,
                        $reminder->user->email,
                        $reminder->stage->getLabel(),
                        $reminder->stageKey,
                    )),
                );

                continue;
            }

            $sent += $scheduler->run($day);
        }

        if ($this->option('dry-run')) {
            $this->info('Próbafutás, nem ment ki levél.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Kiküldött emlékeztetők: %d', $sent));

        return self::SUCCESS;
    }
}
