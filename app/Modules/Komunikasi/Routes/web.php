<?php

use App\Modules\Komunikasi\Controllers\AdminKomunikasiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage komunikasi'])
    ->prefix('komunikasi')
    ->name('komunikasi.')
    ->group(function () {
        Route::get('/', [AdminKomunikasiController::class, 'index'])->name('index');
        Route::get('/trash', [AdminKomunikasiController::class, 'trash'])->name('trash');
        Route::get('/santri/{santri}', [AdminKomunikasiController::class, 'show'])->name('show');
        Route::post('/santri/{santri}', [AdminKomunikasiController::class, 'store'])->name('store');
        Route::patch('/{communication}/read', [AdminKomunikasiController::class, 'markAsRead'])->name('read');
        Route::post('/santri/{santri}/forward/{communication}', [AdminKomunikasiController::class, 'forward'])->name('forward');

        Route::patch('/{communication}/archive', [AdminKomunikasiController::class, 'archive'])->name('archive');
        Route::patch('/{communication}/restore', [AdminKomunikasiController::class, 'restore'])->name('restore');
        Route::delete('/{communication}', [AdminKomunikasiController::class, 'destroy'])->name('destroy');
        Route::delete('/{communication}/force-delete', [AdminKomunikasiController::class, 'forceDelete'])->name('force-delete');

        Route::post('/batch', [AdminKomunikasiController::class, 'batch'])->name('batch');
    });
