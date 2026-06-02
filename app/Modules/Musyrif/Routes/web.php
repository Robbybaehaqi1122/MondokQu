<?php

use App\Modules\Musyrif\Controllers\MusyrifDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Musyrif/Ustadz'])->group(function () {
    Route::get('/musyrif', MusyrifDashboardController::class)->name('musyrif.dashboard');
});
