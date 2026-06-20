<?php

use App\Modules\KeuanganQu\Controllers\BudgetController;
use App\Modules\KeuanganQu\Controllers\CoaAccountController;
use App\Modules\KeuanganQu\Controllers\JournalEntryController;
use App\Modules\KeuanganQu\Controllers\KeuanganQuDashboardController;
use App\Modules\KeuanganQu\Controllers\ReceiptController;
use App\Modules\KeuanganQu\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role_or_permission:Superadmin|Admin|Bendahara|manage keuangan'])
    ->prefix('keuangan')->name('keuangan.')->group(function () {

    Route::get('/dashboard', KeuanganQuDashboardController::class)->name('dashboard');
    Route::redirect('/', '/keuangan/dashboard');

    Route::get('/coa', [CoaAccountController::class, 'index'])->name('coa.index');
    Route::post('/coa', [CoaAccountController::class, 'store'])->name('coa.store');
    Route::patch('/coa/{coaAccount}', [CoaAccountController::class, 'update'])->name('coa.update');
    Route::delete('/coa/{coaAccount}', [CoaAccountController::class, 'destroy'])->name('coa.destroy');

    Route::get('/jurnal', [JournalEntryController::class, 'index'])->name('jurnal.index');
    Route::get('/jurnal/create', [JournalEntryController::class, 'create'])->name('jurnal.create');
    Route::post('/jurnal', [JournalEntryController::class, 'store'])->name('jurnal.store');
    Route::get('/jurnal/{journalEntry}', [JournalEntryController::class, 'show'])->name('jurnal.show');
    Route::delete('/jurnal/{journalEntry}', [JournalEntryController::class, 'destroy'])->name('jurnal.destroy');
    Route::post('/jurnal/{journalEntry}/approve', [JournalEntryController::class, 'approve'])->name('jurnal.approve');

    Route::get('/anggaran', [BudgetController::class, 'index'])->name('anggaran.index');
    Route::post('/anggaran', [BudgetController::class, 'store'])->name('anggaran.store');
    Route::put('/anggaran/{budget}', [BudgetController::class, 'update'])->name('anggaran.update');
    Route::delete('/anggaran/{budget}', [BudgetController::class, 'destroy'])->name('anggaran.destroy');

    Route::get('/laporan', [ReportController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/profit-loss', [ReportController::class, 'profitLoss'])->name('laporan.profit-loss');
    Route::get('/laporan/cash-flow', [ReportController::class, 'cashFlow'])->name('laporan.cash-flow');
    Route::get('/laporan/ledger', [ReportController::class, 'ledger'])->name('laporan.ledger');

    Route::get('/kwitansi', [ReceiptController::class, 'index'])->name('kwitansi.index');
    Route::get('/kwitansi/{journalEntry}/pdf', [ReceiptController::class, 'pdf'])->name('kwitansi.pdf');
    Route::get('/kwitansi/{journalEntry}/download', [ReceiptController::class, 'download'])->name('kwitansi.download');
});
