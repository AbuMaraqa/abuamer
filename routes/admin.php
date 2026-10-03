<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BrandReorderController;
use App\Http\Controllers\Admin\BrandStatusController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CategoryMoveController;
use App\Http\Controllers\Admin\CategoryReorderController;
use App\Http\Controllers\Admin\CategoryStatusController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SlideController;
use App\Http\Controllers\Admin\SlideReorderController;
use App\Http\Controllers\Admin\SlideStatusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Control Panel Routes
|--------------------------------------------------------------------------
|
| Loaded inside the localized route group with the "admin." name prefix,
| the "/{locale}/admin" URL prefix, and the "auth" middleware.
|
*/

Route::get('/', DashboardController::class)->name('dashboard');

// Registered before the resource so "reorder" is not captured as a {category} binding.
Route::patch('categories/reorder', CategoryReorderController::class)->name('categories.reorder');
Route::patch('categories/{category}/move', CategoryMoveController::class)->name('categories.move');
Route::patch('categories/{category}/status', CategoryStatusController::class)->name('categories.status');
Route::resource('categories', CategoryController::class)->except('show');

Route::patch('products/category', ProductCategoryController::class)->name('products.category');
Route::resource('products', ProductController::class)->except('show');

// Registered before the resource so "reorder" is not captured as a {brand} binding.
Route::patch('brands/reorder', BrandReorderController::class)->name('brands.reorder');
Route::patch('brands/{brand}/status', BrandStatusController::class)->name('brands.status');
Route::resource('brands', BrandController::class)->except('show');

// Registered before the resource so "reorder" is not captured as a {slide} binding.
Route::patch('slides/reorder', SlideReorderController::class)->name('slides.reorder');
Route::patch('slides/{slide}/status', SlideStatusController::class)->name('slides.status');
Route::resource('slides', SlideController::class)->except('show');

Route::get('company', [CompanyController::class, 'edit'])->name('company.edit');
Route::put('company', [CompanyController::class, 'update'])->name('company.update');

Route::resource('messages', ContactMessageController::class)->only(['index', 'show', 'destroy']);

Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
