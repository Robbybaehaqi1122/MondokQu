<?php

use App\Modules\Branding\Controllers\TenantBrandingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage branding'])
    ->prefix('branding')
    ->name('branding.')
    ->group(function () {
        Route::get('/', [TenantBrandingController::class, 'edit'])->name('edit');
        Route::post('/', [TenantBrandingController::class, 'update'])->name('update');
        Route::post('/remove-logo', [TenantBrandingController::class, 'removeLogo'])->name('remove-logo');
        Route::post('/remove-favicon', [TenantBrandingController::class, 'removeFavicon'])->name('remove-favicon');
    });
