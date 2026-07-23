<?php

declare(strict_types=1);

use App\Livewire\Products\Support\MaintenanceWindow;
use Illuminate\Support\Facades\Date;

it('opens the window exactly 11 months after the reference date', function (): void {
    $window = new MaintenanceWindow(Date::parse('2025-01-15'));

    expect($window->start()->equalTo(Date::parse('2025-12-15')))->toBeTrue();
});

it('closes the window exactly 13 months after the reference date', function (): void {
    $window = new MaintenanceWindow(Date::parse('2025-01-15'));

    expect($window->end()->equalTo(Date::parse('2026-02-15')))->toBeTrue();
});

it('contains the moment exactly at the window opening boundary', function (): void {
    $window = new MaintenanceWindow(Date::parse('2025-01-15'));

    expect($window->contains(Date::parse('2025-12-15')))->toBeTrue()
        ->and($window->isBeforeWindow(Date::parse('2025-12-15')))->toBeFalse()
        ->and($window->isAfterWindow(Date::parse('2025-12-15')))->toBeFalse();
});

it('contains the moment exactly at the window closing boundary', function (): void {
    $window = new MaintenanceWindow(Date::parse('2025-01-15'));

    expect($window->contains(Date::parse('2026-02-15')))->toBeTrue()
        ->and($window->isBeforeWindow(Date::parse('2026-02-15')))->toBeFalse()
        ->and($window->isAfterWindow(Date::parse('2026-02-15')))->toBeFalse();
});

it('contains a moment strictly inside the window', function (): void {
    $window = new MaintenanceWindow(Date::parse('2025-01-15'));

    expect($window->contains(Date::parse('2026-01-01')))->toBeTrue()
        ->and($window->isBeforeWindow(Date::parse('2026-01-01')))->toBeFalse()
        ->and($window->isAfterWindow(Date::parse('2026-01-01')))->toBeFalse();
});

it('reports a moment one day before the window as before the window', function (): void {
    $window = new MaintenanceWindow(Date::parse('2025-01-15'));

    expect($window->contains(Date::parse('2025-12-14')))->toBeFalse()
        ->and($window->isBeforeWindow(Date::parse('2025-12-14')))->toBeTrue()
        ->and($window->isAfterWindow(Date::parse('2025-12-14')))->toBeFalse();
});

it('reports a moment one day after the window as after the window', function (): void {
    $window = new MaintenanceWindow(Date::parse('2025-01-15'));

    expect($window->contains(Date::parse('2026-02-16')))->toBeFalse()
        ->and($window->isBeforeWindow(Date::parse('2026-02-16')))->toBeFalse()
        ->and($window->isAfterWindow(Date::parse('2026-02-16')))->toBeTrue();
});

it('reports a moment well before the window as before it, never as after it', function (): void {
    $window = new MaintenanceWindow(Date::parse('2025-01-15'));

    expect($window->isBeforeWindow(Date::parse('2025-01-15')))->toBeTrue()
        ->and($window->isAfterWindow(Date::parse('2025-01-15')))->toBeFalse();
});

it('reports a moment well after the window as after it, never as before it', function (): void {
    $window = new MaintenanceWindow(Date::parse('2025-01-15'));

    expect($window->isAfterWindow(Date::parse('2027-01-01')))->toBeTrue()
        ->and($window->isBeforeWindow(Date::parse('2027-01-01')))->toBeFalse();
});
