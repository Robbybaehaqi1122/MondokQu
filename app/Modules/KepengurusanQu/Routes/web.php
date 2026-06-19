<?php

use App\Modules\KepengurusanQu\Controllers\JadwalController;
use App\Modules\KepengurusanQu\Controllers\KepengurusanQuDashboardController;
use App\Modules\KepengurusanQu\Controllers\PengajarController;
use App\Modules\KepengurusanQu\Controllers\PengurusController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage kepengurusan', 'throttle:60,1'])
    ->prefix('kepengurusan')->name('kepengurusan.')->group(function () {
        Route::get('/dashboard', KepengurusanQuDashboardController::class)->name('dashboard');
        Route::redirect('/', '/kepengurusan/dashboard');

        Route::prefix('pengajar')->name('pengajar.')->group(function () {
            Route::get('/', [PengajarController::class, 'index'])->name('index');
            Route::get('/create', [PengajarController::class, 'create'])->name('create');
            Route::post('/', [PengajarController::class, 'store'])->name('store');
            Route::get('/{pengajar}', [PengajarController::class, 'show'])->name('show');
            Route::get('/{pengajar}/edit', [PengajarController::class, 'edit'])->name('edit');
            Route::patch('/{pengajar}', [PengajarController::class, 'update'])->name('update');
            Route::delete('/{pengajar}', [PengajarController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('pengurus')->name('pengurus.')->group(function () {
            Route::get('/', [PengurusController::class, 'index'])->name('index');
            Route::get('/create', [PengurusController::class, 'create'])->name('create');
            Route::post('/', [PengurusController::class, 'store'])->name('store');
            Route::get('/{pengurus}', [PengurusController::class, 'show'])->name('show');
            Route::get('/{pengurus}/edit', [PengurusController::class, 'edit'])->name('edit');
            Route::patch('/{pengurus}', [PengurusController::class, 'update'])->name('update');
            Route::delete('/{pengurus}', [PengurusController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('jadwal')->name('jadwal.')->group(function () {
            Route::get('/', [JadwalController::class, 'index'])->name('index');
            Route::get('/create', [JadwalController::class, 'create'])->name('create');
            Route::post('/', [JadwalController::class, 'store'])->name('store');
            Route::get('/{jadwal}/edit', [JadwalController::class, 'edit'])->name('edit');
            Route::patch('/{jadwal}', [JadwalController::class, 'update'])->name('update');
            Route::delete('/{jadwal}', [JadwalController::class, 'destroy'])->name('destroy');
        });
    });
