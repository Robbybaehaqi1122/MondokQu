<?php

use App\Modules\Saas\Controllers\SaasDashboardController;
use App\Modules\Saas\Controllers\SubscriptionHistoryController;
use App\Modules\Saas\Controllers\TenantManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'role:Superadmin'])->group(function () {
    Route::get('/saas', [SaasDashboardController::class, 'index'])
        ->name('saas.dashboard');
    Route::get('/saas/tenants', [TenantManagementController::class, 'index'])
        ->name('saas.tenants.index');
    Route::get('/saas/subscription-histories', [SubscriptionHistoryController::class, 'index'])
        ->name('saas.subscription-histories.index');
    Route::post('/saas/tenants', [TenantManagementController::class, 'store'])
        ->name('saas.tenants.store');
    Route::get('/saas/tenants/{tenant}', [TenantManagementController::class, 'show'])
        ->name('saas.tenants.show');
    Route::patch('/saas/tenants/{tenant}/subscription', [TenantManagementController::class, 'updateSubscription'])
        ->name('saas.tenants.update-subscription');
});
