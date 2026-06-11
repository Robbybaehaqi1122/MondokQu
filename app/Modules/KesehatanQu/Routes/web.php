<?php

use App\Modules\KesehatanQu\Controllers\KesehatanQuDashboardController;
use App\Modules\KesehatanQu\Controllers\KesehatanQuRekamMedisController;
use App\Modules\KesehatanQu\Controllers\KesehatanQuPemeriksaanController;
use App\Modules\KesehatanQu\Controllers\KesehatanQuObatController;
use App\Modules\KesehatanQu\Controllers\KesehatanQuLaporanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage kesehatan'])
    ->prefix('kesehatan')
    ->name('kesehatan.')
    ->group(function () {
        Route::get('/dashboard', KesehatanQuDashboardController::class)->name('dashboard');

        Route::get('/rekam-medis', [KesehatanQuRekamMedisController::class, 'index'])->name('rekam-medis.index');
        Route::post('/rekam-medis', [KesehatanQuRekamMedisController::class, 'store'])->name('rekam-medis.store');
        Route::get('/rekam-medis/{santri}', [KesehatanQuRekamMedisController::class, 'show'])->name('rekam-medis.show');
        Route::patch('/rekam-medis/{santri}', [KesehatanQuRekamMedisController::class, 'update'])->name('rekam-medis.update');

        Route::get('/pemeriksaan', [KesehatanQuPemeriksaanController::class, 'index'])->name('pemeriksaan.index');
        Route::get('/pemeriksaan/create', [KesehatanQuPemeriksaanController::class, 'create'])->name('pemeriksaan.create');
        Route::post('/pemeriksaan', [KesehatanQuPemeriksaanController::class, 'store'])->name('pemeriksaan.store');
        Route::get('/pemeriksaan/{kesehatanPemeriksaan}', [KesehatanQuPemeriksaanController::class, 'show'])->name('pemeriksaan.show');
        Route::delete('/pemeriksaan/{kesehatanPemeriksaan}', [KesehatanQuPemeriksaanController::class, 'destroy'])->name('pemeriksaan.destroy');

        Route::get('/obat', [KesehatanQuObatController::class, 'index'])->name('obat.index');
        Route::post('/obat', [KesehatanQuObatController::class, 'store'])->name('obat.store');
        Route::patch('/obat/{kesehatanObat}', [KesehatanQuObatController::class, 'update'])->name('obat.update');
        Route::delete('/obat/{kesehatanObat}', [KesehatanQuObatController::class, 'destroy'])->name('obat.destroy');

        Route::get('/laporan', [KesehatanQuLaporanController::class, 'index'])->name('laporan.index');
    });
