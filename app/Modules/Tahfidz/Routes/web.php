<?php

use App\Modules\Tahfidz\Controllers\TahfidzDashboardController;
use App\Modules\Tahfidz\Controllers\TahfidzRaporController;
use App\Modules\Tahfidz\Controllers\TahfidzSetoranController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage tahfidz'])->prefix('tahfidz')->name('tahfidz.')->group(function () {
    Route::get('/dashboard', TahfidzDashboardController::class)->name('dashboard');
    Route::get('/setoran', [TahfidzSetoranController::class, 'index'])->name('setoran.index');
    Route::get('/setoran/create', [TahfidzSetoranController::class, 'create'])->name('setoran.create');
    Route::post('/setoran', [TahfidzSetoranController::class, 'store'])->name('setoran.store');
    Route::get('/setoran/{tahfidzSession}', [TahfidzSetoranController::class, 'show'])->name('setoran.show');
    Route::delete('/setoran/{tahfidzSession}', [TahfidzSetoranController::class, 'destroy'])->name('setoran.destroy');
    Route::get('/rapor', [TahfidzRaporController::class, 'index'])->name('rapor.index');
});
