<?php

use App\Modules\KitabQu\Controllers\KategoriController;
use App\Modules\KitabQu\Controllers\KitabController;
use App\Modules\KitabQu\Controllers\KitabQuDashboardController;
use App\Modules\KitabQu\Controllers\SetoranController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage kitab', 'throttle:60,1'])
    ->prefix('kitab')->name('kitab.')->group(function () {
        Route::get('/dashboard', KitabQuDashboardController::class)->name('dashboard');
        Route::redirect('/', '/kitab/dashboard');

        Route::prefix('kategori')->name('kategori.')->group(function () {
            Route::get('/', [KategoriController::class, 'index'])->name('index');
            Route::post('/', [KategoriController::class, 'store'])->name('store');
            Route::patch('/{kitabKategori}', [KategoriController::class, 'update'])->name('update');
            Route::delete('/{kitabKategori}', [KategoriController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('kitab')->name('kitab.')->group(function () {
            Route::get('/', [KitabController::class, 'index'])->name('index');
            Route::get('/create', [KitabController::class, 'create'])->name('create');
            Route::post('/', [KitabController::class, 'store'])->name('store');
            Route::get('/{kitab}', [KitabController::class, 'show'])->name('show');
            Route::get('/{kitab}/edit', [KitabController::class, 'edit'])->name('edit');
            Route::patch('/{kitab}', [KitabController::class, 'update'])->name('update');
            Route::delete('/{kitab}', [KitabController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('setoran')->name('setoran.')->group(function () {
            Route::get('/', [SetoranController::class, 'index'])->name('index');
            Route::get('/create', [SetoranController::class, 'create'])->name('create');
            Route::post('/', [SetoranController::class, 'store'])->name('store');
            Route::post('/{kitabSetoran}/approve', [SetoranController::class, 'approve'])->name('approve');
            Route::post('/{kitabSetoran}/reject', [SetoranController::class, 'reject'])->name('reject');
            Route::get('/rapor', [SetoranController::class, 'rapor'])->name('rapor');
        });
    });
