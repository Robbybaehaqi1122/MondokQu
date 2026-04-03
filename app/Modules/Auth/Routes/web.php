<?php

use App\Modules\Auth\Controllers\AuthenticatedSessionController;
use App\Modules\Auth\Controllers\PasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::get('/login/check-identity', [AuthenticatedSessionController::class, 'checkIdentity'])
        ->name('login.check-identity');

    Route::post('/login/check-password', [AuthenticatedSessionController::class, 'checkPassword'])
        ->name('login.check-password');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::put('/password', [PasswordController::class, 'update'])
        ->name('password.update');
});
