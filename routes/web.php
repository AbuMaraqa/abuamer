<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Middleware\ShowMaintenancePage;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect'],
], function () {
    Route::middleware(ShowMaintenancePage::class)->group(function () {
        Route::get('/', HomeController::class)->name('home');
        Route::get('about', AboutController::class)->name('about');
        Route::get('contact', [ContactController::class, 'show'])->name('contact');
        Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{path}', [CategoryController::class, 'show'])->where('path', '.+')->name('products.category');
        Route::get('product/{slug}', [ProductController::class, 'show'])->name('products.show');
    });

    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('auth')
        ->group(__DIR__.'/admin.php');
});
