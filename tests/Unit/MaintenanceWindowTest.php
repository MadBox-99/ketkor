<?php

declare(strict_types=1);

use App\Livewire\Products\Support\MaintenanceWindow;
use App\Models\Product;
use Illuminate\Support\Facades\Date;

it('allows maintenance inside the warranty window', function (): void {
    $product = Product::factory()->makeOne(['warrantee_date' => Date::parse('2026-01-01')]);

    expect(MaintenanceWindow::allows($product, Date::parse('2025-12-15')))->toBeTrue()
        ->and(MaintenanceWindow::allows($product, Date::parse('2026-02-15')))->toBeTrue();
});

it('rejects maintenance outside the warranty window', function (): void {
    $product = Product::factory()->makeOne(['warrantee_date' => Date::parse('2026-01-01')]);

    expect(MaintenanceWindow::allows($product, Date::parse('2025-11-01')))->toBeFalse()
        ->and(MaintenanceWindow::allows($product, Date::parse('2026-04-01')))->toBeFalse();
});
