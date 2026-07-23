<?php

declare(strict_types=1);

use App\Enums\MaintenanceReminderStage;
use App\Enums\MaintenanceReminderStatus;
use App\Mail\MaintenanceReminderMail;
use App\Models\MaintenanceReminder;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;
use App\Services\MaintenanceReminderScheduler;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

function sendableProduct(array $attributes = []): Product
{
    $product = Product::factory()->createOne(array_merge([
        'installation_date' => '2024-01-01',
        'warrantee_date' => '2030-01-01',
    ], $attributes));

    ProductLog::factory()->on('2025-03-10 09:00:00')->createOne(['product_id' => $product->id]);
    $product->users()->attach(User::factory()->createOne());

    return $product->fresh();
}

function runOn(string $day): int
{
    return app(MaintenanceReminderScheduler::class)->run(CarbonImmutable::parse($day));
}

it('sends the reminder and logs it as sent', function (): void {
    $product = sendableProduct();

    expect(runOn('2026-02-08'))->toBe(1);

    Mail::assertQueued(MaintenanceReminderMail::class, 1);

    $reminder = MaintenanceReminder::query()->sole();

    expect($reminder->product_id)->toBe($product->id)
        ->and($reminder->stage)->toBe(MaintenanceReminderStage::Advance)
        ->and($reminder->stage_key)->toBe(30)
        ->and($reminder->status)->toBe(MaintenanceReminderStatus::Sent)
        ->and($reminder->due_date->toDateString())->toBe('2026-03-10')
        ->and($reminder->last_maintenance_at->toDateString())->toBe('2025-03-10')
        ->and($reminder->sent_at)->not->toBeNull()
        ->and($reminder->email)->toBe($product->users()->first()->email);
});

it('does not send the same reminder twice', function (): void {
    sendableProduct();

    runOn('2026-02-08');
    expect(runOn('2026-02-08'))->toBe(0);

    Mail::assertQueued(MaintenanceReminderMail::class, 1);
    expect(MaintenanceReminder::query()->count())->toBe(1);
});

it('sends the 7 day reminder after the 30 day one', function (): void {
    sendableProduct();

    runOn('2026-02-08');
    runOn('2026-03-03');

    Mail::assertQueued(MaintenanceReminderMail::class, 2);
    expect(MaintenanceReminder::query()->count())->toBe(2);
});

it('logs a failure and keeps processing the other products', function (): void {
    sendableProduct(['serial_number' => 'FAIL-0001']);
    sendableProduct(['serial_number' => 'OK-0001']);

    Mail::shouldReceive('to')->andReturnUsing(function (string $email) {
        static $call = 0;
        $call++;

        if ($call === 1) {
            throw new RuntimeException('SMTP connection refused');
        }

        return Mockery::mock(['send' => null, 'queue' => null]);
    });

    expect(runOn('2026-02-08'))->toBe(1);

    $failed = MaintenanceReminder::query()
        ->where('status', MaintenanceReminderStatus::Failed)
        ->sole();

    expect($failed->error)->toContain('SMTP connection refused')
        ->and($failed->sent_at)->toBeNull()
        ->and(MaintenanceReminder::query()->count())->toBe(2);
});

it('retries a previously failed reminder without creating a duplicate row', function (): void {
    $product = sendableProduct();

    MaintenanceReminder::query()->create([
        'product_id' => $product->id,
        'user_id' => $product->users()->first()->id,
        'email' => $product->users()->first()->email,
        'stage' => MaintenanceReminderStage::Advance,
        'stage_key' => 30,
        'due_date' => '2026-03-10',
        'last_maintenance_at' => '2025-03-10',
        'status' => MaintenanceReminderStatus::Failed,
        'error' => 'SMTP connection refused',
    ]);

    expect(runOn('2026-02-08'))->toBe(1)
        ->and(MaintenanceReminder::query()->count())->toBe(1)
        ->and(MaintenanceReminder::query()->sole()->status)->toBe(MaintenanceReminderStatus::Sent)
        ->and(MaintenanceReminder::query()->sole()->error)->toBeNull();
});

it('stops the overdue series once a new maintenance log is recorded', function (): void {
    $product = sendableProduct();

    runOn('2026-03-24');
    expect(MaintenanceReminder::query()->count())->toBe(1);

    ProductLog::factory()->on('2026-03-25 09:00:00')->createOne(['product_id' => $product->id]);

    expect(runOn('2026-04-07'))->toBe(0);
});

it('sends manually regardless of the day, and allows repeated manual sends', function (): void {
    $product = sendableProduct();
    $scheduler = app(MaintenanceReminderScheduler::class);

    expect($scheduler->sendManually($product))->toBe(1)
        ->and($scheduler->sendManually($product))->toBe(1);

    Mail::assertQueued(MaintenanceReminderMail::class, 2);

    $reminders = MaintenanceReminder::query()
        ->where('stage', MaintenanceReminderStage::Manual)
        ->orderBy('stage_key')
        ->get();

    expect($reminders)->toHaveCount(2)
        ->and($reminders->pluck('stage_key')->all())->toBe([1, 2]);
});

it('refuses a manual send for an ineligible product', function (): void {
    $product = sendableProduct(['warrantee_date' => '2020-01-01']);

    expect(app(MaintenanceReminderScheduler::class)->sendManually($product))->toBe(0);

    Mail::assertNothingQueued();
});
