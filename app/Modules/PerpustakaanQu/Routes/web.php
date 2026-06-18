<?php

use App\Modules\PerpustakaanQu\Controllers\KategoriController;
use App\Modules\PerpustakaanQu\Controllers\KitabController;
use App\Modules\PerpustakaanQu\Controllers\PeminjamanController;
use App\Modules\PerpustakaanQu\Controllers\PerpustakaanQuDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage perpustakaan'])
    ->prefix('perpustakaan')->name('perpustakaan.')->group(function () {

    Route::get('/dashboard', PerpustakaanQuDashboardController::class)->name('dashboard');
    Route::redirect('/', '/perpustakaan/dashboard');

    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
    Route::put('/kategori/{perpustakaanKategori}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{perpustakaanKategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

    Route::get('/kitab', [KitabController::class, 'index'])->name('kitab.index');
    Route::get('/kitab/create', [KitabController::class, 'create'])->name('kitab.create');
    Route::post('/kitab', [KitabController::class, 'store'])->name('kitab.store');
    Route::get('/kitab/{perpustakaanKitab}', [KitabController::class, 'show'])->name('kitab.show');
    Route::get('/kitab/{perpustakaanKitab}/edit', [KitabController::class, 'edit'])->name('kitab.edit');
    Route::put('/kitab/{perpustakaanKitab}', [KitabController::class, 'update'])->name('kitab.update');
    Route::delete('/kitab/{perpustakaanKitab}', [KitabController::class, 'destroy'])->name('kitab.destroy');

    Route::get('/peminjaman', [PeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::get('/peminjaman/create', [PeminjamanController::class, 'create'])->name('peminjaman.create');
    Route::post('/peminjaman', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    Route::post('/peminjaman/{perpustakaanPeminjaman}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('peminjaman.kembalikan');
    Route::delete('/peminjaman/{perpustakaanPeminjaman}', [PeminjamanController::class, 'destroy'])->name('peminjaman.destroy');
});
