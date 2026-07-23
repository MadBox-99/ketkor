<?php

declare(strict_types=1);

use App\Enums\ProductLogType;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;

it('defaults products to yearly maintenance with reminders enabled', function (): void {
    $product = Product::factory()->createOne()->fresh();

    expect($product->maintenance_interval_months)->toBe(12)
        ->and($product->maintenance_reminders_enabled)->toBeTrue();
});

it('allows a half-yearly interval and disabling reminders per product', function (): void {
    $product = Product::factory()->createOne([
        'maintenance_interval_months' => 6,
        'maintenance_reminders_enabled' => false,
    ]);

    expect($product->fresh()->maintenance_interval_months)->toBe(6)
        ->and($product->fresh()->maintenance_reminders_enabled)->toBeFalse();
});

it('defaults users to reminders enabled', function (): void {
    expect(User::factory()->createOne()->fresh()->maintenance_reminders_enabled)->toBeTrue();
});

it('creates maintenance product logs by default', function (): void {
    $log = ProductLog::factory()->createOne();

    expect($log->what)->toBe(ProductLogType::Maintenance)
        ->and($log->when)->not->toBeNull();
});

it('creates installation product logs via the state', function (): void {
    expect(ProductLog::factory()->installation()->createOne()->what)
        ->toBe(ProductLogType::Installation);
});
