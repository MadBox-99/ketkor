<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('does not register routes for deleted controllers', function (string $name): void {
    expect(Route::has($name))->toBeFalse();
})->with([
    'partials.index',
    'partials.create',
    'partials.store',
    'partials.show',
    'partials.edit',
    'partials.update',
    'partials.destroy',
    'productlogs.index',
    'productlogs.create',
    'productlogs.store',
    'productlogs.show',
    'productlogs.edit',
    'productlogs.update',
    'productlogs.destroy',
    'organizations.show',
    'tools.show',
]);

it('does not ship controllers that have no routes', function (string $class): void {
    expect(class_exists($class))->toBeFalse();
})->with([
    'App\Http\Controllers\LogController',
    'App\Http\Controllers\PartialController',
    'App\Http\Controllers\ProductLogController',
]);
