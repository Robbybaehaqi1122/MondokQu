<?php

use App\Modules\Pengurus\Controllers\OperationalReportController;
use App\Modules\Pengurus\Controllers\PengurusDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Pengurus'])->group(function () {
    Route::get('/pengurus', [PengurusDashboardController::class, 'index'])->name('pengurus.dashboard');
    Route::redirect('/pengurus/santri', '/santri')->name('pengurus.santri');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage kamar|create izin|approve izin'])
    ->prefix('pengurus/laporan')
    ->name('pengurus.reports.')
    ->group(function () {
        Route::get('/', [OperationalReportController::class, 'index'])->name('index');
    });
