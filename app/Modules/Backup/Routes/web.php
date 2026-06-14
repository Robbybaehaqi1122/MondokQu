<?php

use App\Modules\Backup\Controllers\BackupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage backups'])->group(function () {
    Route::get('/backups', [BackupController::class, 'index'])->name('backup.index');
    Route::post('/backups', [BackupController::class, 'store'])->name('backup.store');
    Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backup.download');
    Route::post('/backups/{backup}/mark-failed', [BackupController::class, 'markFailed'])->name('backup.mark-failed');
    Route::delete('/backups/{backup}', [BackupController::class, 'destroy'])->name('backup.destroy');
});
