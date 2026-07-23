<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('keeps only the auth endpoint controllers', function (): void {
    $controllers = collect(glob(app_path('Http/Controllers/*.php')))
        ->map(fn (string $path): string => basename($path))
        ->sort()
        ->values()
        ->all();

    expect($controllers)->toBe(['Controller.php']);
});

it('routes every web page through a Livewire component', function (string $name): void {
    expect(Route::has($name))->toBeTrue();
})->with([
    'index',
    'products.index',
    'products.search',
    'products.myproducts',
    'products.edit',
    'tools.index',
    'tools.create',
    'tools.edit',
    'organizations.index',
    'organizations.create',
    'organizations.edit',
    'organizations.myorganization',
    'organizations.employee.create',
    'profile.edit',
]);
