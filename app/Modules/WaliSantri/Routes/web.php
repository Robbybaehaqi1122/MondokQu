<?php

use App\Modules\WaliSantri\Controllers\WaliSantriDashboardController;
use App\Modules\WaliSantri\Controllers\WaliSantriKomunikasiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role_or_permission:Wali Santri|view portal wali', 'throttle:60,1'])->group(function () {
    Route::get('/wali-santri', [WaliSantriDashboardController::class, 'index'])->name('wali-santri.dashboard');
    Route::get('/wali-santri/tagihan/{invoice}', [WaliSantriDashboardController::class, 'showInvoice'])->name('wali-santri.invoices.show');
    Route::post('/wali-santri/tagihan/{invoice}/bukti-bayar', [WaliSantriDashboardController::class, 'storePaymentConfirmation'])->name('wali-santri.invoices.payment-confirmations.store');
    Route::get('/wali-santri/tagihan/{invoice}/kwitansi', [WaliSantriDashboardController::class, 'printInvoice'])->name('wali-santri.invoices.receipt');

    Route::get('/wali-santri/santri/{santri}/profil', [WaliSantriDashboardController::class, 'profilSantri'])->name('wali-santri.profil-santri');
    Route::get('/wali-santri/santri/{santri}/absensi', [WaliSantriDashboardController::class, 'riwayatAbsensi'])->name('wali-santri.absensi');
    Route::get('/wali-santri/santri/{santri}/pelanggaran', [WaliSantriDashboardController::class, 'riwayatPelanggaran'])->name('wali-santri.pelanggaran');
    Route::get('/wali-santri/santri/{santri}/tahfidz', [WaliSantriDashboardController::class, 'riwayatTahfidz'])->name('wali-santri.tahfidz');
    Route::get('/wali-santri/santri/{santri}/akademik', [WaliSantriDashboardController::class, 'riwayatAkademik'])->name('wali-santri.akademik');
    Route::get('/wali-santri/santri/{santri}/rapor', [WaliSantriDashboardController::class, 'raporSantri'])->name('wali-santri.rapor');

    Route::get('/wali-santri/komunikasi', [WaliSantriKomunikasiController::class, 'index'])->name('wali-santri.komunikasi.index');
    Route::get('/wali-santri/komunikasi/{santri}', [WaliSantriKomunikasiController::class, 'show'])->name('wali-santri.komunikasi.show');
    Route::post('/wali-santri/komunikasi', [WaliSantriKomunikasiController::class, 'store'])->name('wali-santri.komunikasi.store');
});
