<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Akademik\Controllers\AkademikDashboardController;
use App\Modules\Akademik\Controllers\MataPelajaranController;
use App\Modules\Akademik\Controllers\NilaiSantriController;
use App\Modules\Akademik\Controllers\RaporController;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage akademik'])
    ->prefix('akademik')
    ->name('akademik.')
    ->group(function () {
        Route::get('/dashboard', AkademikDashboardController::class)->name('dashboard');

        Route::get('/mata-pelajaran', [MataPelajaranController::class, 'index'])->name('mata-pelajaran.index');
        Route::post('/mata-pelajaran', [MataPelajaranController::class, 'store'])->name('mata-pelajaran.store');
        Route::put('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'update'])->name('mata-pelajaran.update');
        Route::delete('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'destroy'])->name('mata-pelajaran.destroy');

        Route::get('/nilai', [NilaiSantriController::class, 'index'])->name('nilai.index');
        Route::get('/nilai/create', [NilaiSantriController::class, 'create'])->name('nilai.create');
        Route::post('/nilai', [NilaiSantriController::class, 'store'])->name('nilai.store');
        Route::get('/nilai/{nilaiSantri}/edit', [NilaiSantriController::class, 'edit'])->name('nilai.edit');
        Route::put('/nilai/{nilaiSantri}', [NilaiSantriController::class, 'update'])->name('nilai.update');
        Route::delete('/nilai/{nilaiSantri}', [NilaiSantriController::class, 'destroy'])->name('nilai.destroy');

        Route::get('/rapor', [RaporController::class, 'index'])->name('rapor.index');
        Route::get('/rapor/show', [RaporController::class, 'show'])->name('rapor.show');
        Route::get('/rapor/pdf', [RaporController::class, 'exportPdf'])->name('rapor.pdf');
        Route::get('/rapor/chart-data', [RaporController::class, 'chartData'])->name('rapor.chart-data');
    });
