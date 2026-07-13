<?php

use App\Modules\Akademik\Controllers\AkademikDashboardController;
use App\Modules\Akademik\Controllers\AttitudeGradeController;
use App\Modules\Akademik\Controllers\GradeLevelController;
use App\Modules\Akademik\Controllers\MataPelajaranController;
use App\Modules\Akademik\Controllers\NilaiSantriController;
use App\Modules\Akademik\Controllers\NilaiSikapController;
use App\Modules\Akademik\Controllers\RaporController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage akademik'])
    ->prefix('akademik')
    ->name('akademik.')
    ->group(function () {
        Route::get('/dashboard', AkademikDashboardController::class)->name('dashboard');

        Route::post('/grade-level', [GradeLevelController::class, 'store'])->name('grade-level.store');
        Route::put('/grade-level/{gradeLevel}/toggle', [GradeLevelController::class, 'toggle'])->name('grade-level.toggle');
        Route::delete('/grade-level/{gradeLevel}', [GradeLevelController::class, 'destroy'])->name('grade-level.destroy');

        Route::get('/mata-pelajaran', [MataPelajaranController::class, 'index'])->name('mata-pelajaran.index');
        Route::post('/mata-pelajaran', [MataPelajaranController::class, 'store'])->name('mata-pelajaran.store');
        Route::put('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'update'])->name('mata-pelajaran.update');
        Route::delete('/mata-pelajaran/{mataPelajaran}', [MataPelajaranController::class, 'destroy'])->name('mata-pelajaran.destroy');
        Route::post('/mata-pelajaran/clone', [MataPelajaranController::class, 'clone'])->name('mata-pelajaran.clone');

        Route::get('/nilai', [NilaiSantriController::class, 'index'])->name('nilai.index');
        Route::get('/nilai/create', [NilaiSantriController::class, 'create'])->name('nilai.create');
        Route::post('/nilai', [NilaiSantriController::class, 'store'])->name('nilai.store');
        Route::get('/nilai/{nilaiSantri}/edit', [NilaiSantriController::class, 'edit'])->name('nilai.edit');
        Route::put('/nilai/{nilaiSantri}', [NilaiSantriController::class, 'update'])->name('nilai.update');
        Route::delete('/nilai/{nilaiSantri}', [NilaiSantriController::class, 'destroy'])->name('nilai.destroy');

        Route::get('/nilai-sikap', [AttitudeGradeController::class, 'index'])->name('attitude.index');
        Route::get('/nilai-sikap/input', [AttitudeGradeController::class, 'create'])->name('attitude.create');
        Route::post('/nilai-sikap', [AttitudeGradeController::class, 'store'])->name('attitude.store');
        Route::get('/nilai-sikap/show', [AttitudeGradeController::class, 'show'])->name('attitude.show');

        Route::get('/nilai-sikap-akhlak', [NilaiSikapController::class, 'index'])->name('nilai-sikap.index');
        Route::get('/nilai-sikap-akhlak/create', [NilaiSikapController::class, 'create'])->name('nilai-sikap.create');
        Route::post('/nilai-sikap-akhlak', [NilaiSikapController::class, 'store'])->name('nilai-sikap.store');
        Route::get('/nilai-sikap-akhlak/show', [NilaiSikapController::class, 'show'])->name('nilai-sikap.show');
        Route::get('/nilai-sikap-akhlak/{nilaiSikap}/edit', [NilaiSikapController::class, 'edit'])->name('nilai-sikap.edit');
        Route::put('/nilai-sikap-akhlak/{nilaiSikap}', [NilaiSikapController::class, 'update'])->name('nilai-sikap.update');
        Route::delete('/nilai-sikap-akhlak/{nilaiSikap}', [NilaiSikapController::class, 'destroy'])->name('nilai-sikap.destroy');

        Route::get('/rapor', [RaporController::class, 'index'])->name('rapor.index');
        Route::get('/rapor/show', [RaporController::class, 'show'])->name('rapor.show');
        Route::get('/rapor/pdf', [RaporController::class, 'exportPdf'])->name('rapor.pdf');
        Route::get('/rapor/chart-data', [RaporController::class, 'chartData'])->name('rapor.chart-data');
    });
