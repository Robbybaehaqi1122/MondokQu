<?php

use App\Modules\Tahfidz\Controllers\TahfidzDashboardController;
use App\Modules\Tahfidz\Controllers\TahfidzRaporController;
use App\Modules\Tahfidz\Controllers\TahfidzSetoranController;
use App\Modules\Tahfidz\Controllers\TahfidzTargetController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage tahfidz'])->prefix('tahfidz')->name('tahfidz.')->group(function () {
    Route::get('/dashboard', TahfidzDashboardController::class)->name('dashboard');
    Route::get('/setoran', [TahfidzSetoranController::class, 'index'])->name('setoran.index');
    Route::get('/setoran/create', [TahfidzSetoranController::class, 'create'])->name('setoran.create');
    Route::post('/setoran', [TahfidzSetoranController::class, 'store'])->name('setoran.store');
    Route::get('/setoran/{tahfidzSession}', [TahfidzSetoranController::class, 'show'])->name('setoran.show');
    Route::get('/setoran/{tahfidzSession}/edit', [TahfidzSetoranController::class, 'edit'])->name('setoran.edit');
    Route::put('/setoran/{tahfidzSession}', [TahfidzSetoranController::class, 'update'])->name('setoran.update');
    Route::delete('/setoran/{tahfidzSession}', [TahfidzSetoranController::class, 'destroy'])->name('setoran.destroy');
    Route::get('/rapor', [TahfidzRaporController::class, 'index'])->name('rapor.index');
    Route::get('/targets', [TahfidzTargetController::class, 'index'])->name('targets.index');
    Route::get('/targets/create', [TahfidzTargetController::class, 'create'])->name('targets.create');
    Route::post('/targets', [TahfidzTargetController::class, 'store'])->name('targets.store');
    Route::get('/targets/{tahfidzTarget}/edit', [TahfidzTargetController::class, 'edit'])->name('targets.edit');
    Route::put('/targets/{tahfidzTarget}', [TahfidzTargetController::class, 'update'])->name('targets.update');
    Route::delete('/targets/{tahfidzTarget}', [TahfidzTargetController::class, 'destroy'])->name('targets.destroy');
});
