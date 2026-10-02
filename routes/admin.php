<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CategoryMoveController;
use App\Http\Controllers\Admin\CategoryReorderController;
use App\Http\Controllers\Admin\CategoryStatusController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
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
