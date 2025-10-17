<?php

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PartialController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ToolController;
use App\Livewire\ProductFilamentTable;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';
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
    Route::get('/', function () {
        return view('index');
    })->name('index');

    // Route::resource('/products', ProductController::class);

    Route::resource('organizations', OrganizationController::class);
    Route::resource('productlogs', ProductLogController::class);
    Route::resource('partials', PartialController::class);
    Route::resource('tools', ToolController::class);
    Route::resource('logs', LogController::class);

    Route::prefix('organization')->name('organizations.')->group(function (): void {
        Route::get('/{user}/{organization}/{product}', [OrganizationController::class, 'removeUserProduct'])->name('detach');
        Route::middleware(['role:Organizer'])->group(function (): void {
            Route::put('/store', [EmployeeController::class, 'store'])->name('store');
            Route::get('/create', [EmployeeController::class, 'create'])->name('create');
            Route::get('/myorganization', [OrganizationController::class, 'myOrganization'])->name('myorganization');
            Route::post('/move', [OrganizationController::class, 'productMove'])->name('productMove');
            Route::put('/myorganizationupdate/{organization}', [OrganizationController::class, 'myOrganizationUpdate'])->name('myorganizationupdate');
            Route::get('/removeUserFromOrganization/{user}', [OrganizationController::class, 'removeUserFromOrganization'])->name('removeUserFromOrganization');
        });
    });

    Route::prefix('product')->name('products.')->group(function (): void {
        Route::get('/', ProductFilamentTable::class)->name('index');
        Route::get('/search', [ProductController::class, 'search'])->name('search');
        Route::get('/myproducts', [ProductController::class, 'myproducts'])->name('myproducts');
        Route::get('/add/{product}', [ProductController::class, 'add'])->name('add');
        Route::delete('/remove/{product}', [ProductController::class, 'remove'])->name('remove');
        Route::get('/partialUpdate/{product}', [ProductController::class, 'partialUpdate']);
    });
    Route::prefix('accestoken')->name('accestokens.')->group(function (): void {
        Route::get('/createAccessToken/{product}', [AccessTokenController::class, 'createAccessToken'])->name('createAccessToken');
        Route::get('/activateAccessToken/{token}', [AccessTokenController::class, 'activateAccessToken'])->name('activateAccessToken');
    });
    Route::prefix('user')->name('users.')->group(function (): void {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit/{user}', [ProfileController::class, 'show'])->name('show');
        Route::get('/create', [ProfileController::class, 'create'])->name('create');
        Route::put('/store', [ProfileController::class, 'store'])->name('store');
        Route::put('/update/{user}', [ProfileController::class, 'userUpdate'])->name('update');
    });
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
