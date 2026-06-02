<?php

use App\Modules\Payment\Controllers\SantriPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'throttle:60,1'])->group(function () {
    Route::prefix('santri/pembayaran')->name('santri.payments.')->group(function () {
        Route::get('/', [SantriPaymentController::class, 'index'])
            ->middleware('permission:view pembayaran')
            ->name('index');

        Route::get('/tagihan', [SantriPaymentController::class, 'invoices'])
            ->middleware('permission:view pembayaran')
            ->name('invoices');

        Route::get('/tagihan/export', [SantriPaymentController::class, 'exportInvoices'])
            ->middleware('permission:view pembayaran')
            ->name('invoices.export');

        Route::post('/tagihan', [SantriPaymentController::class, 'storeInvoice'])
            ->middleware('permission:create pembayaran')
            ->name('invoices.store');

        Route::post('/tagihan/bulanan', [SantriPaymentController::class, 'generateMonthlyInvoices'])
            ->middleware('permission:create pembayaran')
            ->name('invoices.monthly.generate');

        Route::patch('/tagihan/{invoice}', [SantriPaymentController::class, 'updateInvoice'])
            ->middleware('permission:update pembayaran')
            ->name('invoices.update');

        Route::delete('/tagihan/{invoice}', [SantriPaymentController::class, 'destroyInvoice'])
            ->middleware('permission:update pembayaran')
            ->name('invoices.destroy');

        Route::post('/tagihan/{invoice}/payments', [SantriPaymentController::class, 'storePayment'])
            ->middleware('permission:create pembayaran')
            ->name('payments.store');

        Route::patch('/payments/{payment}', [SantriPaymentController::class, 'updatePayment'])
            ->middleware('permission:edit historical pembayaran')
            ->name('payments.update');

        Route::delete('/payments/{payment}', [SantriPaymentController::class, 'destroyPayment'])
            ->middleware('permission:edit historical pembayaran')
            ->name('payments.destroy');

        Route::get('/laporan', [SantriPaymentController::class, 'reports'])
            ->middleware('permission:view laporan keuangan')
            ->name('reports');

        Route::get('/laporan/export', [SantriPaymentController::class, 'exportReports'])
            ->middleware('permission:view laporan keuangan')
            ->name('reports.export');
    });
});
