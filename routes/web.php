<?php

declare(strict_types=1);

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Home;
use App\Livewire\Organizations;
use App\Livewire\Products\Edit;
use App\Livewire\Products\Index;
use App\Livewire\Products\MyProducts;
use App\Livewire\Products\Search;
use App\Livewire\Tools;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::livewire('/', Home::class)->name('index');

    Route::livewire('organizations', Organizations\Index::class)->name('organizations.index');
    Route::get('organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::put('organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');

    Route::livewire('tools', Tools\Index::class)->name('tools.index');
    Route::livewire('tools/create', Tools\Create::class)->name('tools.create');
    Route::livewire('tools/{tool}/edit', Tools\Edit::class)->name('tools.edit');

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
        Route::livewire('/', Index::class)->name('index');
        Route::livewire('/search', Search::class)->name('search');
        Route::livewire('/myproducts', MyProducts::class)->name('myproducts');
        Route::livewire('/edit/{product}', Edit::class)->name('edit');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
