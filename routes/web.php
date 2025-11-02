<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CategorySettingsController;
use App\Http\Controllers\ProductSettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/products/{id_product}', [ProductController::class, 'index'])->name('products.details');

Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('/settings/categories', [CategorySettingsController::class,'index'])->name('settings.categories.index');

    //crud categorias
    Route::get('/settings/categories/create', [CategorySettingsController::class, 'create'])->name('settings.categories.create');
    Route::post('/settings/categories', [CategorySettingsController::class, 'store'])->name('settings.categories.store');
    Route::get('/settings/categories/{category:id}/edit', [CategorySettingsController::class, 'edit'])->name('settings.categories.edit');
    Route::put('/settings/categories/{category:id}', [CategorySettingsController::class, 'update'])->name('settings.categories.update');
    Route::delete('/settings/categories/{category:id}', [CategorySettingsController::class, 'destroy'])->name('settings.categories.destroy');

    //crud productos

    Route::get('/settings/products', [ProductSettingsController::class, 'index'])->name('settings.products.index');
    Route::get('/settings/products/create', [ProductSettingsController::class, 'create'])->name('settings.products.create');
    Route::post('/settings/products', [ProductSettingsController::class, 'store'])->name('settings.products.store');
    Route::get('/settings/products/{product:id}/edit', [ProductSettingsController::class, 'edit'])->name('settings.products.edit');
    Route::put('/settings/products/{product:id}', [ProductSettingsController::class, 'update'])->name('settings.products.update');
    Route::delete('/settings/products/{product:id}', [ProductSettingsController::class, 'destroy'])->name('settings.products.destroy');
});

require __DIR__.'/auth.php';
