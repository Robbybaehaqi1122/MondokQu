<?php

use App\Modules\Bendahara\Controllers\BendaharaDashboardController;
use App\Modules\Bendahara\Controllers\BendaharaLaporanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Bendahara'])->group(function () {
    Route::get('/bendahara', BendaharaDashboardController::class)->name('bendahara.dashboard');
    Route::get('/bendahara/laporan', BendaharaLaporanController::class)->name('bendahara.laporan');
});
