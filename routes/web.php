<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PermissionManagementController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Pengurus\PengurusDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SantriManagementController;
use App\Http\Controllers\SantriPaymentController;
use App\Http\Controllers\SubscriptionStatusController;
use App\Http\Controllers\TenantImpersonationController;
use App\Modules\Auth\Actions\DetermineDashboardRouteAction;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (DetermineDashboardRouteAction $determineDashboardRoute) {
    return redirect($determineDashboardRoute->handle(auth()->user()));
})->middleware(['auth', 'password_change_required'])->name('dashboard');

Route::get('/dashboard/home', function () {
    return view('dashboard');
})->middleware(['auth', 'password_change_required', 'subscription_active', 'verified'])->name('dashboard.home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/subscription/expired', [SubscriptionStatusController::class, 'showExpired'])->name('subscription.expired');
    Route::post('/impersonation/stop', [TenantImpersonationController::class, 'destroy'])->name('impersonation.stop');
});

// Role-based access routes
Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Superadmin|Admin'])->group(function () {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::get('/admin/users/{user}', [UserManagementController::class, 'show'])->name('admin.users.show');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::patch('/admin/users/{user}', [UserManagementController::class, 'updateProfile'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.update-role');
    Route::patch('/admin/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('admin.users.update-status');
    Route::post('/admin/users/{user}/email/resend-verification', [UserManagementController::class, 'resendVerification'])->name('admin.users.resend-verification');
    Route::patch('/admin/users/{user}/email/verify', [UserManagementController::class, 'verifyEmail'])->name('admin.users.verify-email');
    Route::patch('/admin/users/{user}/password', [UserManagementController::class, 'updatePassword'])->name('admin.users.update-password');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:assign roles'])->group(function () {
    Route::get('/admin/roles', [RoleManagementController::class, 'index'])->name('admin.roles');
    Route::post('/admin/roles', [RoleManagementController::class, 'store'])->name('admin.roles.store');
    Route::patch('/admin/roles/{role}/permissions', [RoleManagementController::class, 'updatePermissions'])->name('admin.roles.update-permissions');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage system settings'])->group(function () {
    Route::get('/admin/permissions', [PermissionManagementController::class, 'index'])->name('admin.permissions');
    Route::post('/admin/permissions', [PermissionManagementController::class, 'store'])->name('admin.permissions.store');
    Route::patch('/admin/permissions/{permission}', [PermissionManagementController::class, 'update'])->name('admin.permissions.update');
    Route::patch('/admin/permissions/{permission}/roles', [PermissionManagementController::class, 'updateRoles'])->name('admin.permissions.update-roles');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role_or_permission:Superadmin|view activity logs'])->group(function () {
    Route::get('/admin/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Superadmin'])->group(function () {
    Route::delete('/admin/activity-logs', [ActivityLogController::class, 'destroyAll'])->name('admin.activity-logs.destroy-all');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified'])->group(function () {
    Route::prefix('santri/pembayaran')->name('santri.payments.')->group(function () {
        Route::get('/', [SantriPaymentController::class, 'index'])
            ->middleware('permission:view pembayaran')
            ->name('index');

        Route::get('/tagihan', [SantriPaymentController::class, 'invoices'])
            ->middleware('permission:view pembayaran')
            ->name('invoices');

        Route::post('/tagihan', [SantriPaymentController::class, 'storeInvoice'])
            ->middleware('permission:create pembayaran')
            ->name('invoices.store');

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
    });
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:view santri'])->group(function () {
    Route::get('/santri', [SantriManagementController::class, 'index'])->name('santri.index');
    Route::get('/santri/{santri}', [SantriManagementController::class, 'show'])->name('santri.show');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:create santri'])->group(function () {
    Route::post('/santri', [SantriManagementController::class, 'store'])->name('santri.store');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:update santri'])->group(function () {
    Route::patch('/santri/{santri}', [SantriManagementController::class, 'update'])->name('santri.update');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:delete santri'])->group(function () {
    Route::delete('/santri/{santri}', [SantriManagementController::class, 'destroy'])->name('santri.destroy');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Pengurus'])->group(function () {
    Route::get('/pengurus', [PengurusDashboardController::class, 'index'])->name('pengurus.dashboard');
    Route::redirect('/pengurus/santri', '/santri')->name('pengurus.santri');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Musyrif/Ustadz'])->group(function () {
    Route::get('/musyrif', fn () => view('dashboard'))->name('musyrif.dashboard');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Bendahara'])->group(function () {
    Route::get('/bendahara', fn () => view('bendahara.dashboard'))->name('bendahara.dashboard');
    Route::get('/bendahara/laporan', fn () => view('bendahara.laporan'))->name('bendahara.laporan');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Wali Santri'])->group(function () {
    Route::get('/wali-santri', fn () => view('dashboard'))->name('wali-santri.dashboard');
});

require base_path('app/Modules/Auth/Routes/web.php');
require base_path('app/Modules/Saas/Routes/web.php');
