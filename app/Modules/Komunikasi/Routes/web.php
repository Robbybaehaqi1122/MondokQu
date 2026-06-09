<?php

use App\Modules\Komunikasi\Controllers\AdminKomunikasiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage komunikasi'])
    ->prefix('komunikasi')
    ->name('komunikasi.')
    ->group(function () {
        Route::get('/', [AdminKomunikasiController::class, 'index'])->name('index');
        Route::get('/santri/{santri}', [AdminKomunikasiController::class, 'show'])->name('show');
        Route::post('/santri/{santri}', [AdminKomunikasiController::class, 'store'])->name('store');
        Route::patch('/{communication}/read', [AdminKomunikasiController::class, 'markAsRead'])->name('read');
        Route::post('/santri/{santri}/forward/{communication}', [AdminKomunikasiController::class, 'forward'])->name('forward');
    });
