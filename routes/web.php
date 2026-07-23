<?php

declare(strict_types=1);

use App\Http\Controllers\EmployeeController;
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
    Route::livewire('organizations/create', Organizations\Create::class)->name('organizations.create');
    Route::livewire('organizations/{organization}/edit', Organizations\Edit::class)->name('organizations.edit');

    Route::livewire('tools', Tools\Index::class)->name('tools.index');
    Route::livewire('tools/create', Tools\Create::class)->name('tools.create');
    Route::livewire('tools/{tool}/edit', Tools\Edit::class)->name('tools.edit');

    Route::prefix('organization')->name('organizations.')->group(function (): void {
        Route::middleware(['role:Organizer|Admin|Super Admin'])->group(function (): void {
            Route::put('/store', [EmployeeController::class, 'store'])->name('employee.store');
            Route::get('/create', [EmployeeController::class, 'create'])->name('employee.create');
            Route::livewire('/myorganization', Organizations\MyOrganization::class)->name('myorganization');
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
