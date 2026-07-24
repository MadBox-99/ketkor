<?php

declare(strict_types=1);

use App\Mail\MaintenanceReminderMail;
use App\Models\MaintenanceReminder;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

beforeEach(function (): void {
    Mail::fake();
});

function commandProduct(): Product
{
    $product = Product::factory()->createOne([
        'installation_date' => '2024-01-01',
        'warrantee_date' => '2030-01-01',
    ]);

    ProductLog::factory()->on('2025-03-10 09:00:00')->createOne(['product_id' => $product->id]);
    $product->users()->attach(User::factory()->createOne());

    return $product->fresh();
}

it('sends the reminders due today', function (): void {
    /** @var TestCase $this */
    commandProduct();
    $this->travelTo('2026-02-08 08:00:00');

    $this->artisan('maintenance:send-reminders')
        ->expectsOutputToContain('1')
        ->assertExitCode(0);

    Mail::assertQueued(MaintenanceReminderMail::class, 1);
});

it('sends nothing and logs nothing in dry run mode', function (): void {
    /** @var TestCase $this */
    commandProduct();
    $this->travelTo('2026-02-08 08:00:00');

    $this->artisan('maintenance:send-reminders', ['--dry-run' => true])
        ->assertExitCode(0);

    Mail::assertNothingQueued();
    expect(MaintenanceReminder::query()->count())->toBe(0);
});

it('catches up a missed day', function (): void {
    /** @var TestCase $this */
    commandProduct();
    $this->travelTo('2026-02-09 08:00:00');

    $this->artisan('maintenance:send-reminders', ['--catch-up' => 1])
        ->assertExitCode(0);

    Mail::assertQueued(MaintenanceReminderMail::class, 1);
    expect(MaintenanceReminder::query()->sole()->stage_key)->toBe(30);
});

it('does not resend on catch up when the reminder already went out', function (): void {
    /** @var TestCase $this */
    commandProduct();

    $this->travelTo('2026-02-08 08:00:00');
    $this->artisan('maintenance:send-reminders')->assertExitCode(0);

    $this->travelTo('2026-02-09 08:00:00');
    $this->artisan('maintenance:send-reminders', ['--catch-up' => 1])->assertExitCode(0);

    Mail::assertQueued(MaintenanceReminderMail::class, 1);
    expect(MaintenanceReminder::query()->count())->toBe(1);
});

it('is scheduled to run daily', function (): void {
    $events = collect(resolve(Schedule::class)->events())
        ->filter(fn (Event $event): bool => str_contains(
            (string) $event->command,
            'maintenance:send-reminders',
        ));

    $event = $events->first();

    expect($events)->toHaveCount(1)
        ->and($event->expression)->toBe('0 8 * * *')
        ->and($event->withoutOverlapping)->toBeTrue();
});
