<?php

declare(strict_types=1);

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ToolController;
use App\Livewire\Index;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

require __DIR__ . '/auth.php';
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Livewire's Finder resolves a class named "Index" as a sub-namespace's
// implicit index component and strips it to an empty name, which breaks
// full-page resolution for a top-level `App\Livewire\Index`. Registering
// the component name explicitly avoids that name-generation bug.
Livewire::component('index', Index::class);

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::livewire('/', Index::class)->name('index');

    Route::get('organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::put('organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');

    Route::get('tools', [ToolController::class, 'index'])->name('tools.index');
    Route::get('tools/create', [ToolController::class, 'create'])->name('tools.create');
    Route::post('tools', [ToolController::class, 'store'])->name('tools.store');
    Route::get('tools/{tool}/edit', [ToolController::class, 'edit'])->name('tools.edit');
    Route::put('tools/{tool}', [ToolController::class, 'update'])->name('tools.update');
    Route::delete('tools/{tool}', [ToolController::class, 'destroy'])->name('tools.destroy');

    Route::prefix('organization')->name('organizations.')->group(function (): void {
        Route::get('/{user}/{organization}/{product}', [OrganizationController::class, 'removeUserProduct'])->name('detach');
        Route::middleware(['role:Organizer|Admin|Super Admin'])->group(function (): void {
            Route::put('/store', [EmployeeController::class, 'store'])->name('employee.store');
            Route::get('/create', [EmployeeController::class, 'create'])->name('employee.create');
            Route::get('/myorganization', [OrganizationController::class, 'myOrganization'])->name('myorganization');
            Route::post('/move', [OrganizationController::class, 'productMove'])->name('productMove');
            Route::put('/myorganizationupdate/{organization}', [OrganizationController::class, 'myOrganizationUpdate'])->name('myorganizationupdate');
            Route::get('/removeUserFromOrganization/{user}', [OrganizationController::class, 'removeUserFromOrganization'])->name('removeUserFromOrganization');
        });
    });

    Route::prefix('product')->name('products.')->group(function (): void {
        Route::get('/search', [ProductController::class, 'search'])->name('search');
        Route::get('/myproducts', [ProductController::class, 'myproducts'])->name('myproducts');
        Route::get('/add/{product}', [ProductController::class, 'add'])->name('add');
        Route::delete('/remove/{product}', [ProductController::class, 'remove'])->name('remove');
        Route::get('/edit/{product}', [ProductController::class, 'edit'])->name('edit');
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::put('/update/{product}', [ProductController::class, 'update'])->name('update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
