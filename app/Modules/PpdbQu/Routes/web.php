<?php

use App\Modules\PpdbQu\Controllers\CetakController;
use App\Modules\PpdbQu\Controllers\GelombangController;
use App\Modules\PpdbQu\Controllers\PendaftaranController;
use App\Modules\PpdbQu\Controllers\PengumumanController;
use App\Modules\PpdbQu\Controllers\PpdbQuDashboardController;
use App\Modules\PpdbQu\Controllers\PpdbQuNotificationController;
use App\Modules\PpdbQu\Controllers\SeleksiController;
use Illuminate\Support\Facades\Route;

Route::get('/ppdb/daftar/{gelombang?}', [PendaftaranController::class, 'create'])->name('ppdb.daftar');
Route::post('/ppdb/daftar', [PendaftaranController::class, 'store'])->name('ppdb.daftar.store');

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage ppdb'])
    ->prefix('ppdb')->name('ppdb.')->group(function () {

    Route::get('/dashboard', PpdbQuDashboardController::class)->name('dashboard');
    Route::redirect('/', '/ppdb/dashboard');

    Route::get('/gelombang', [GelombangController::class, 'index'])->name('gelombang.index');
    Route::get('/gelombang/create', [GelombangController::class, 'create'])->name('gelombang.create');
    Route::post('/gelombang', [GelombangController::class, 'store'])->name('gelombang.store');
    Route::get('/gelombang/{ppdbGelombang}/edit', [GelombangController::class, 'edit'])->name('gelombang.edit');
    Route::put('/gelombang/{ppdbGelombang}', [GelombangController::class, 'update'])->name('gelombang.update');
    Route::delete('/gelombang/{ppdbGelombang}', [GelombangController::class, 'destroy'])->name('gelombang.destroy');

    Route::get('/pendaftaran', [PendaftaranController::class, 'index'])->name('pendaftaran.index');
    Route::get('/pendaftaran/{ppdbPendaftaran}', [PendaftaranController::class, 'show'])->name('pendaftaran.show');
    Route::put('/pendaftaran/{ppdbPendaftaran}', [PendaftaranController::class, 'update'])->name('pendaftaran.update');
    Route::delete('/pendaftaran/{ppdbPendaftaran}', [PendaftaranController::class, 'destroy'])->name('pendaftaran.destroy');
    Route::post('/pendaftaran/{ppdbPendaftaran}/daftar-ulang', [PendaftaranController::class, 'daftarUlang'])->name('pendaftaran.daftar-ulang');

    Route::get('/seleksi', [SeleksiController::class, 'index'])->name('seleksi.index');
    Route::post('/seleksi/{ppdbPendaftaran}', [SeleksiController::class, 'store'])->name('seleksi.store');
    Route::put('/seleksi/{ppdbSeleksi}', [SeleksiController::class, 'update'])->name('seleksi.update');
    Route::delete('/seleksi/{ppdbSeleksi}', [SeleksiController::class, 'destroy'])->name('seleksi.destroy');

    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
    Route::get('/pengumuman/create', [PengumumanController::class, 'create'])->name('pengumuman.create');
    Route::post('/pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');
    Route::get('/pengumuman/{ppdbPengumuman}', [PengumumanController::class, 'show'])->name('pengumuman.show');
    Route::post('/pengumuman/{ppdbPengumuman}/publish', [PengumumanController::class, 'publish'])->name('pengumuman.publish');
    Route::delete('/pengumuman/{ppdbPengumuman}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');

    Route::get('/cetak/formulir/{ppdbPendaftaran}', [CetakController::class, 'formulir'])->name('cetak.formulir');
    Route::get('/cetak/kartu/{ppdbPendaftaran}', [CetakController::class, 'kartuPeserta'])->name('cetak.kartu');
    Route::get('/cetak/surat-terima/{ppdbPendaftaran}', [CetakController::class, 'suratTerima'])->name('cetak.surat-terima');

    Route::get('/notifikasi', [PpdbQuNotificationController::class, 'index'])->name('notifikasi.index');
    Route::get('/notifikasi/unread-count', [PpdbQuNotificationController::class, 'unreadCount'])->name('notifikasi.unread-count');
});
