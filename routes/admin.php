<?php

use App\Http\Controllers\Admin\DashboardController;
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
