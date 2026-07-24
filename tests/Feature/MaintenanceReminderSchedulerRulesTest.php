<?php

declare(strict_types=1);

use App\Enums\MaintenanceReminderStage;
use App\Models\MaintenanceReminderSetting;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;
use App\Services\MaintenanceReminderScheduler;
use App\Support\MaintenanceSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Készülék 2025-03-10-i karbantartással, tehát 2026-03-10-i esedékességgel,
 * élő garanciával és egy értesíthető címzettel.
 */
function eligibleProduct(array $attributes = []): Product
{
    $product = Product::factory()->createOne(array_merge([
        'installation_date' => '2024-01-01',
        'warrantee_date' => '2030-01-01',
    ], $attributes));

    ProductLog::factory()->on('2025-03-10 09:00:00')->createOne(['product_id' => $product->id]);
    $product->users()->attach(User::factory()->createOne());

    return $product->fresh();
}

function scheduler(): MaintenanceReminderScheduler
{
    return resolve(MaintenanceReminderScheduler::class);
}

function pendingOn(string $day): Collection
{
    return scheduler()->pendingFor(CarbonImmutable::parse($day));
}

it('queues an advance reminder 30 days before the due date', function (): void {
    eligibleProduct();

    $pending = pendingOn('2026-02-08');

    expect($pending)->toHaveCount(1)
        ->and($pending->first()->stage)->toBe(MaintenanceReminderStage::Advance)
        ->and($pending->first()->stageKey)->toBe(30)
        ->and($pending->first()->schedule->dueDate->toDateString())->toBe('2026-03-10');
});

it('queues an advance reminder 7 days before the due date', function (): void {
    eligibleProduct();

    $pending = pendingOn('2026-03-03');

    expect($pending)->toHaveCount(1)
        ->and($pending->first()->stageKey)->toBe(7);
});

it('queues nothing on a day that matches no rule', function (): void {
    eligibleProduct();

    expect(pendingOn('2026-02-20'))->toBeEmpty()
        ->and(pendingOn('2026-03-10'))->toBeEmpty();
});

it('queues overdue reminders every 14 days, at most three times', function (): void {
    eligibleProduct();

    expect(pendingOn('2026-03-24')->first()->stage)->toBe(MaintenanceReminderStage::Overdue)
        ->and(pendingOn('2026-03-24')->first()->stageKey)->toBe(1)
        ->and(pendingOn('2026-04-07')->first()->stageKey)->toBe(2)
        ->and(pendingOn('2026-04-21')->first()->stageKey)->toBe(3)
        ->and(pendingOn('2026-05-05'))->toBeEmpty();
});

it('honours a configured advance schedule', function (): void {
    MaintenanceReminderSetting::current()->update(['advance_days' => [45]]);
    eligibleProduct();

    expect(pendingOn('2026-02-08'))->toBeEmpty()
        ->and(pendingOn('2026-01-24')->first()->stageKey)->toBe(45);
});

it('skips everything when the global switch is off', function (): void {
    MaintenanceReminderSetting::current()->update(['enabled' => false]);
    eligibleProduct();

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('skips products with reminders disabled', function (): void {
    eligibleProduct(['maintenance_reminders_enabled' => false]);

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('skips recipients with reminders disabled', function (): void {
    $product = eligibleProduct();
    $product->users()->first()->update(['maintenance_reminders_enabled' => false]);

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('skips products whose warranty has expired', function (): void {
    eligibleProduct(['warrantee_date' => '2026-01-01']);

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('skips products with no warranty date', function (): void {
    eligibleProduct(['warrantee_date' => null]);

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('skips products with no recipient', function (): void {
    $product = eligibleProduct();
    $product->users()->detach();

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('skips recipients without an email address', function (): void {
    $product = eligibleProduct();
    $product->users()->first()->update(['email' => '']);

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('falls back to the installation date when there is no maintenance log', function (): void {
    $product = Product::factory()->createOne([
        'installation_date' => '2025-05-20',
        'warrantee_date' => '2030-01-01',
    ]);
    $product->users()->attach(User::factory()->createOne());

    expect(pendingOn('2026-04-20')->first()->schedule->dueDate->toDateString())
        ->toBe('2026-05-20');
});

it('skips products with neither a maintenance log nor an installation date', function (): void {
    $product = Product::factory()->createOne([
        'installation_date' => null,
        'warrantee_date' => '2030-01-01',
    ]);
    $product->users()->attach(User::factory()->createOne());

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('queues one reminder per recipient', function (): void {
    $product = eligibleProduct();
    $product->users()->attach(User::factory()->createOne());

    expect(pendingOn('2026-02-08'))->toHaveCount(2);
});

it('does not duplicate a recipient linked both directly and through the pivot', function (): void {
    $product = eligibleProduct();
    $user = $product->users()->first();
    $product->update(['user_id' => $user->id]);

    expect(pendingOn('2026-02-08'))->toHaveCount(1);
});

it('isProductEligible: reports an eligible product on a normal day as eligible', function (): void {
    $product = eligibleProduct();

    expect(scheduler()->isProductEligible($product, CarbonImmutable::parse('2026-02-08')))->toBeTrue();
});

it('isProductEligible: reports ineligible when the global switch is off', function (): void {
    MaintenanceReminderSetting::current()->update(['enabled' => false]);
    $product = eligibleProduct();

    expect(scheduler()->isProductEligible($product, CarbonImmutable::parse('2026-02-08')))->toBeFalse();
});

it('isProductEligible: reports ineligible when the product\'s own reminders are disabled', function (): void {
    $product = eligibleProduct(['maintenance_reminders_enabled' => false]);

    expect(scheduler()->isProductEligible($product, CarbonImmutable::parse('2026-02-08')))->toBeFalse();
});

it('isProductEligible: reports ineligible when the warranty date is null', function (): void {
    $product = eligibleProduct(['warrantee_date' => null]);

    expect(scheduler()->isProductEligible($product, CarbonImmutable::parse('2026-02-08')))->toBeFalse();
});

it('isProductEligible: reports ineligible when the warranty has expired', function (): void {
    $product = eligibleProduct(['warrantee_date' => '2026-01-01']);

    expect(scheduler()->isProductEligible($product, CarbonImmutable::parse('2026-02-08')))->toBeFalse();
});

it('isProductEligible: treats a warranty expiring exactly on the processed day as still eligible, agreeing with pendingFor', function (): void {
    $product = eligibleProduct(['warrantee_date' => '2026-02-08']);

    expect(scheduler()->isProductEligible($product, CarbonImmutable::parse('2026-02-08')))->toBeTrue()
        ->and(pendingOn('2026-02-08'))->toHaveCount(1);
});

it('resolveStage: resolving settings itself matches passing them explicitly', function (): void {
    $product = eligibleProduct();
    $schedule = MaintenanceSchedule::for($product);
    $day = CarbonImmutable::parse('2026-02-08');

    $withExplicitSettings = scheduler()->resolveStage($schedule, $day, MaintenanceReminderSetting::current());
    $withDefaultSettings = scheduler()->resolveStage($schedule, $day);

    expect($withDefaultSettings)->toBe($withExplicitSettings)
        ->and($withDefaultSettings)->not->toBeNull();
});
