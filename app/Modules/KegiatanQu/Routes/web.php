<?php

use App\Modules\KegiatanQu\Controllers\KegiatanController;
use App\Modules\KegiatanQu\Controllers\KegiatanQuDashboardController;
use App\Modules\KegiatanQu\Controllers\LaporanController;
use App\Modules\KegiatanQu\Controllers\NilaiController;
use App\Modules\KegiatanQu\Controllers\PendaftaranController;
use App\Modules\KegiatanQu\Controllers\PertemuanController;
use App\Modules\KegiatanQu\Controllers\PresensiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage kegiatan'])
    ->prefix('kegiatan')->name('kegiatan.')->group(function () {

    Route::get('/dashboard', KegiatanQuDashboardController::class)->name('dashboard');
    Route::redirect('/', '/kegiatan/dashboard');

    Route::get('/kegiatan', [KegiatanController::class, 'index'])->name('kegiatan.index');
    Route::get('/kegiatan/create', [KegiatanController::class, 'create'])->name('kegiatan.create');
    Route::post('/kegiatan', [KegiatanController::class, 'store'])->name('kegiatan.store');
    Route::get('/kegiatan/{kegiatan}', [KegiatanController::class, 'show'])->name('kegiatan.show');
    Route::get('/kegiatan/{kegiatan}/edit', [KegiatanController::class, 'edit'])->name('kegiatan.edit');
    Route::put('/kegiatan/{kegiatan}', [KegiatanController::class, 'update'])->name('kegiatan.update');
    Route::delete('/kegiatan/{kegiatan}', [KegiatanController::class, 'destroy'])->name('kegiatan.destroy');

    Route::get('/pendaftaran', [PendaftaranController::class, 'index'])->name('pendaftaran.index');
    Route::post('/pendaftaran', [PendaftaranController::class, 'store'])->name('pendaftaran.store');
    Route::put('/pendaftaran/{kegiatanPendaftaran}', [PendaftaranController::class, 'update'])->name('pendaftaran.update');
    Route::delete('/pendaftaran/{kegiatanPendaftaran}', [PendaftaranController::class, 'destroy'])->name('pendaftaran.destroy');

    Route::get('/pertemuan', [PertemuanController::class, 'index'])->name('pertemuan.index');
    Route::get('/pertemuan/create', [PertemuanController::class, 'create'])->name('pertemuan.create');
    Route::post('/pertemuan', [PertemuanController::class, 'store'])->name('pertemuan.store');
    Route::get('/pertemuan/{kegiatanPertemuan}', [PertemuanController::class, 'show'])->name('pertemuan.show');
    Route::get('/pertemuan/{kegiatanPertemuan}/edit', [PertemuanController::class, 'edit'])->name('pertemuan.edit');
    Route::put('/pertemuan/{kegiatanPertemuan}', [PertemuanController::class, 'update'])->name('pertemuan.update');
    Route::delete('/pertemuan/{kegiatanPertemuan}', [PertemuanController::class, 'destroy'])->name('pertemuan.destroy');

    Route::post('/presensi/{kegiatanPertemuan}', [PresensiController::class, 'store'])->name('presensi.store');
    Route::put('/presensi/{kegiatanPertemuan}', [PresensiController::class, 'update'])->name('presensi.update');

    Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
    Route::post('/nilai', [NilaiController::class, 'store'])->name('nilai.store');
    Route::put('/nilai/{kegiatanNilai}', [NilaiController::class, 'update'])->name('nilai.update');
    Route::delete('/nilai/{kegiatanNilai}', [NilaiController::class, 'destroy'])->name('nilai.destroy');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/kehadiran', [LaporanController::class, 'kehadiran'])->name('laporan.kehadiran');
    Route::get('/laporan/nilai', [LaporanController::class, 'nilai'])->name('laporan.nilai');
});
