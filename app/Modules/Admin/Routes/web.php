<?php

use App\Modules\Admin\Controllers\ActivityLogController;
use App\Modules\Admin\Controllers\AdminDashboardController;
use App\Modules\Admin\Controllers\AuditLogController;
use App\Modules\Admin\Controllers\PermissionManagementController;
use App\Modules\Admin\Controllers\RoleManagementController;
use App\Modules\Admin\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Superadmin|Admin', 'throttle:60,1'])->group(function () {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::get('/admin/users/{user}', [UserManagementController::class, 'show'])->name('admin.users.show');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::patch('/admin/users/{user}', [UserManagementController::class, 'updateProfile'])->name('admin.users.update');
    Route::patch('/admin/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.update-role');
    Route::patch('/admin/users/{user}/status', [UserManagementController::class, 'updateStatus'])->name('admin.users.update-status');
    Route::patch('/admin/users/{user}/guardian-santri', [UserManagementController::class, 'updateGuardianSantri'])->name('admin.users.update-guardian-santri');
    Route::post('/admin/users/{user}/email/resend-verification', [UserManagementController::class, 'resendVerification'])->name('admin.users.resend-verification');
    Route::patch('/admin/users/{user}/email/verify', [UserManagementController::class, 'verifyEmail'])->name('admin.users.verify-email');
    Route::patch('/admin/users/{user}/password', [UserManagementController::class, 'updatePassword'])->name('admin.users.update-password');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/admin/users/{user}/permissions', [UserManagementController::class, 'updatePermissions'])->name('admin.users.update-permissions');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:assign roles', 'throttle:60,1'])->group(function () {
    Route::get('/admin/roles', [RoleManagementController::class, 'index'])->name('admin.roles');
    Route::post('/admin/roles', [RoleManagementController::class, 'store'])->name('admin.roles.store');
    Route::patch('/admin/roles/{role}/permissions', [RoleManagementController::class, 'updatePermissions'])->name('admin.roles.update-permissions');
    Route::post('/admin/roles/{role}/sync-from-template', [RoleManagementController::class, 'syncFromTemplate'])->name('admin.roles.sync-from-template');
    Route::post('/admin/roles/sync-tenant/{tenant}', [RoleManagementController::class, 'syncTenantRoles'])->name('admin.roles.sync-tenant');
    Route::post('/admin/roles/sync-all-tenants', [RoleManagementController::class, 'syncAllTenants'])->name('admin.roles.sync-tenant-all');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage system settings', 'throttle:60,1'])->group(function () {
    Route::get('/admin/permissions', [PermissionManagementController::class, 'index'])->name('admin.permissions');
    Route::post('/admin/permissions', [PermissionManagementController::class, 'store'])->name('admin.permissions.store');
    Route::patch('/admin/permissions/{permission}', [PermissionManagementController::class, 'update'])->name('admin.permissions.update');
    Route::patch('/admin/permissions/{permission}/roles', [PermissionManagementController::class, 'updateRoles'])->name('admin.permissions.update-roles');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role_or_permission:Superadmin|view activity logs', 'throttle:60,1'])->group(function () {
    Route::get('/admin/activity-logs', [ActivityLogController::class, 'index'])->name('admin.activity-logs');
    Route::get('/admin/activity-logs/export', [ActivityLogController::class, 'export'])->name('admin.activity-logs.export')->middleware('throttle:5,1');
    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Superadmin', 'password.confirm', 'throttle:10,1'])->group(function () {
    Route::delete('/admin/activity-logs', [ActivityLogController::class, 'destroyAll'])->name('admin.activity-logs.destroy-all');
});
