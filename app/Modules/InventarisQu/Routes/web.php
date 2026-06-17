<?php

use App\Modules\InventarisQu\Controllers\AsetController;
use App\Modules\InventarisQu\Controllers\InventarisQuDashboardController;
use App\Modules\InventarisQu\Controllers\KategoriController;
use App\Modules\InventarisQu\Controllers\LaporanController;
use App\Modules\InventarisQu\Controllers\LokasiController;
use App\Modules\InventarisQu\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage inventaris'])
    ->prefix('inventaris')->name('inventaris.')->group(function () {

    Route::get('/dashboard', InventarisQuDashboardController::class)->name('dashboard');
    Route::redirect('/', '/inventaris/dashboard');

    Route::get('/aset', [AsetController::class, 'index'])->name('aset.index');
    Route::get('/aset/create', [AsetController::class, 'create'])->name('aset.create');
    Route::post('/aset', [AsetController::class, 'store'])->name('aset.store');
    Route::get('/aset/{aset}', [AsetController::class, 'show'])->name('aset.show');
    Route::get('/aset/{aset}/edit', [AsetController::class, 'edit'])->name('aset.edit');
    Route::put('/aset/{aset}', [AsetController::class, 'update'])->name('aset.update');
    Route::delete('/aset/{aset}', [AsetController::class, 'destroy'])->name('aset.destroy');
    Route::post('/aset/{aset}/qr', [AsetController::class, 'generateQr'])->name('aset.qr');

    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::put('/kategori/{kategoriAset}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{kategoriAset}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

    Route::get('/lokasi', [LokasiController::class, 'index'])->name('lokasi.index');
    Route::post('/lokasi', [LokasiController::class, 'store'])->name('lokasi.store');
    Route::put('/lokasi/{lokasiAset}', [LokasiController::class, 'update'])->name('lokasi.update');
    Route::delete('/lokasi/{lokasiAset}', [LokasiController::class, 'destroy'])->name('lokasi.destroy');

    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
    Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    Route::post('/peminjaman/{peminjamanAset}/kembali', [PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembali');
    Route::delete('/peminjaman/{peminjamanAset}', [PeminjamanController::class, 'destroy'])->name('peminjaman.destroy');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/per-lokasi', [LaporanController::class, 'perLokasi'])->name('laporan.per-lokasi');
    Route::get('/laporan/per-kategori', [LaporanController::class, 'perKategori'])->name('laporan.per-kategori');
    Route::get('/laporan/kondisi', [LaporanController::class, 'kondisi'])->name('laporan.kondisi');
});
