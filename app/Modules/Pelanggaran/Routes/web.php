<?php

use App\Modules\Pelanggaran\Controllers\PelanggaranController;
use App\Modules\Pelanggaran\Controllers\PelanggaranDashboardController;
use App\Modules\Pelanggaran\Controllers\PelanggaranKategoriController;
use App\Modules\Pelanggaran\Controllers\PelanggaranLaporanController;
use App\Modules\Pelanggaran\Controllers\SanctionThresholdController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage pelanggaran'])->prefix('pelanggaran')->name('pelanggaran.')->group(function () {
    Route::get('/dashboard', PelanggaranDashboardController::class)->name('dashboard');
    Route::get('/', [PelanggaranController::class, 'index'])->name('index');
    Route::get('/create', [PelanggaranController::class, 'create'])->name('create');
    Route::post('/', [PelanggaranController::class, 'store'])->name('store');
    Route::delete('/{pelanggaran}', [PelanggaranController::class, 'destroy'])->name('destroy');
    Route::get('/laporan', [PelanggaranLaporanController::class, 'index'])->name('laporan.index');
    Route::resource('kategori', PelanggaranKategoriController::class)->except(['create', 'edit', 'show'])->parameters(['kategori' => 'pelanggaranKategori']);

    Route::get('/sanction-thresholds', [SanctionThresholdController::class, 'index'])->name('sanction-thresholds.index');
    Route::post('/sanction-thresholds', [SanctionThresholdController::class, 'store'])->name('sanction-thresholds.store');
    Route::put('/sanction-thresholds/{sanctionThreshold}', [SanctionThresholdController::class, 'update'])->name('sanction-thresholds.update');
    Route::delete('/sanction-thresholds/{sanctionThreshold}', [SanctionThresholdController::class, 'destroy'])->name('sanction-thresholds.destroy');
});
