<?php

use App\Http\Controllers\Portal\LoginController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\PasswordChangeController;
use Illuminate\Support\Facades\Route;

// Rutas públicas del portal (sin autenticación)
Route::prefix('portal')->name('portal.')->group(function () {

    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Rutas protegidas por guard web_externo
    Route::middleware('auth.externo')->group(function () {

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('password/change', [PasswordChangeController::class, 'show'])->name('password.change');
        Route::post('password/change', [PasswordChangeController::class, 'update'])->name('password.update');

    });
});
