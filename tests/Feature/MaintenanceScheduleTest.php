<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductLog;
use App\Support\MaintenanceSchedule;
use Carbon\CarbonImmutable;

it('counts twelve months from the last maintenance log', function (): void {
    $product = Product::factory()->createOne(['installation_date' => '2024-01-01']);
    ProductLog::factory()->on('2025-03-10 09:00:00')->createOne(['product_id' => $product->id]);

    $schedule = MaintenanceSchedule::for($product->fresh());

    expect($schedule->dueDate->toDateString())->toBe('2026-03-10')
        ->and($schedule->fromMaintenanceLog)->toBeTrue();
});

it('uses the latest maintenance log when several exist', function (): void {
    $product = Product::factory()->createOne(['installation_date' => '2024-01-01']);
    ProductLog::factory()->on('2024-06-01 09:00:00')->createOne(['product_id' => $product->id]);
    ProductLog::factory()->on('2025-08-20 09:00:00')->createOne(['product_id' => $product->id]);

    expect(MaintenanceSchedule::for($product->fresh())->dueDate->toDateString())
        ->toBe('2026-08-20');
});

it('ignores non-maintenance logs', function (): void {
    $product = Product::factory()->createOne(['installation_date' => '2024-01-01']);
    ProductLog::factory()->installation()->on('2025-09-09 09:00:00')
        ->createOne(['product_id' => $product->id]);

    $schedule = MaintenanceSchedule::for($product->fresh());

    expect($schedule->dueDate->toDateString())->toBe('2025-01-01')
        ->and($schedule->fromMaintenanceLog)->toBeFalse();
});

it('falls back to the installation date when there is no maintenance log', function (): void {
    $product = Product::factory()->createOne(['installation_date' => '2025-02-14']);

    expect(MaintenanceSchedule::for($product->fresh())->dueDate->toDateString())->toBe('2026-02-14');
});

it('honours the half-yearly interval', function (): void {
    $product = Product::factory()->createOne([
        'installation_date' => '2025-02-14',
        'maintenance_interval_months' => 6,
    ]);

    expect(MaintenanceSchedule::for($product)->dueDate->toDateString())->toBe('2025-08-14');
});

it('returns null when neither a maintenance log nor an installation date exists', function (): void {
    $product = Product::factory()->createOne(['installation_date' => null]);

    expect(MaintenanceSchedule::for($product))->toBeNull()
        ->and($product->nextMaintenanceDueDate())->toBeNull();
});

it('exposes the due date through the product', function (): void {
    $product = Product::factory()->createOne(['installation_date' => '2025-02-14']);

    $product = $product->fresh();

    expect($product->nextMaintenanceDueDate())->toBeInstanceOf(CarbonImmutable::class)
        ->and($product->nextMaintenanceDueDate()->toDateString())->toBe('2026-02-14');
});

it('clamps a month-end base date to the end of the target month', function (): void {
    $product = Product::factory()->createOne(['installation_date' => '2025-01-01']);
    ProductLog::factory()->on('2025-08-31 09:00:00')->createOne(['product_id' => $product->id]);

    $product = $product->fresh();
    $product->update(['maintenance_interval_months' => 6]);

    expect(MaintenanceSchedule::for($product->fresh())->dueDate->toDateString())
        ->toBe('2026-02-28');
});

it('clamps a leap day base date on a yearly interval', function (): void {
    $product = Product::factory()->createOne(['installation_date' => '2023-01-01']);
    ProductLog::factory()->on('2024-02-29 09:00:00')->createOne(['product_id' => $product->id]);

    expect(MaintenanceSchedule::for($product->fresh())->dueDate->toDateString())
        ->toBe('2025-02-28');
});
