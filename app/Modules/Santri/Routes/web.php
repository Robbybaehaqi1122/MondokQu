<?php

use App\Modules\Santri\Controllers\SantriManagementController;
use Illuminate\Support\Facades\Route;

// Permission-based routes
Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:view santri', 'throttle:120,1'])->group(function () {
    Route::get('/santri', [SantriManagementController::class, 'index'])->name('santri.index');
    Route::get('/santri/export', [SantriManagementController::class, 'export'])->name('santri.export');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:view santri', 'throttle:120,1'])->group(function () {
    Route::get('/santri/{santri}', [SantriManagementController::class, 'show'])->name('santri.show');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:create santri', 'throttle:60,1'])->group(function () {
    Route::post('/santri', [SantriManagementController::class, 'store'])->name('santri.store');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:update santri', 'throttle:60,1'])->group(function () {
    Route::patch('/santri/{santri}', [SantriManagementController::class, 'update'])->name('santri.update');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:delete santri', 'throttle:60,1'])->group(function () {
    Route::delete('/santri/{santri}', [SantriManagementController::class, 'destroy'])->name('santri.destroy');
});
