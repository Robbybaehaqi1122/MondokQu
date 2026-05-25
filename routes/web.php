<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PermissionManagementController;
use App\Http\Controllers\Admin\RoleManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\AttendanceActivityController;
use App\Http\Controllers\AttendanceDashboardController;
use App\Http\Controllers\AttendanceRecordController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\AttendanceSessionController;
use App\Http\Controllers\DataExportDownloadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Pengurus\LeaveRequestController;
use App\Http\Controllers\Pengurus\OperationalReportController;
use App\Http\Controllers\Pengurus\PengurusDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomManagementController;
use App\Http\Controllers\SantriManagementController;
use App\Http\Controllers\SantriPaymentController;
use App\Http\Controllers\SubscriptionStatusController;
use App\Http\Controllers\TenantImpersonationController;
use App\Http\Controllers\WaliSantriDashboardController;
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

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/subscription/expired', [SubscriptionStatusController::class, 'showExpired'])->name('subscription.expired');
    Route::post('/impersonation/stop', [TenantImpersonationController::class, 'destroy'])->name('impersonation.stop');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'throttle:60,1'])->group(function () {
    Route::get('/exports/{dataExport}/download', DataExportDownloadController::class)->name('exports.download');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
});

// Role-based access routes
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
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:assign roles', 'throttle:60,1'])->group(function () {
    Route::get('/admin/roles', [RoleManagementController::class, 'index'])->name('admin.roles');
    Route::post('/admin/roles', [RoleManagementController::class, 'store'])->name('admin.roles.store');
    Route::patch('/admin/roles/{role}/permissions', [RoleManagementController::class, 'updatePermissions'])->name('admin.roles.update-permissions');
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
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Superadmin', 'password.confirm', 'throttle:10,1'])->group(function () {
    Route::delete('/admin/activity-logs', [ActivityLogController::class, 'destroyAll'])->name('admin.activity-logs.destroy-all');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'throttle:60,1'])->group(function () {
    Route::prefix('absen')->name('attendance.')->middleware('permission:manage absensi')->group(function () {
        Route::get('/', [AttendanceDashboardController::class, 'index'])->name('dashboard');

        Route::get('/kegiatan', [AttendanceActivityController::class, 'index'])->name('activities.index');
        Route::post('/kegiatan', [AttendanceActivityController::class, 'store'])->name('activities.store');
        Route::patch('/kegiatan/{attendanceActivity}', [AttendanceActivityController::class, 'update'])->name('activities.update');
        Route::delete('/kegiatan/{attendanceActivity}', [AttendanceActivityController::class, 'destroy'])->name('activities.destroy');

        Route::get('/laporan', [AttendanceReportController::class, 'index'])->name('reports.index');

        Route::get('/sesi', [AttendanceSessionController::class, 'index'])->name('sessions.index');
        Route::post('/sesi', [AttendanceSessionController::class, 'store'])->name('sessions.store');
        Route::get('/sesi/{attendanceSession}/input', [AttendanceRecordController::class, 'edit'])->name('sessions.records.edit');
        Route::put('/sesi/{attendanceSession}/input', [AttendanceRecordController::class, 'update'])->name('sessions.records.update');
        Route::patch('/sesi/{attendanceSession}', [AttendanceSessionController::class, 'update'])->name('sessions.update');
        Route::delete('/sesi/{attendanceSession}', [AttendanceSessionController::class, 'destroy'])->name('sessions.destroy');
    });

    Route::prefix('santri/pembayaran')->name('santri.payments.')->group(function () {
        Route::get('/', [SantriPaymentController::class, 'index'])
            ->middleware('permission:view pembayaran')
            ->name('index');

        Route::get('/tagihan', [SantriPaymentController::class, 'invoices'])
            ->middleware('permission:view pembayaran')
            ->name('invoices');

        Route::get('/tagihan/export', [SantriPaymentController::class, 'exportInvoices'])
            ->middleware('permission:view pembayaran')
            ->name('invoices.export');

        Route::post('/tagihan', [SantriPaymentController::class, 'storeInvoice'])
            ->middleware('permission:create pembayaran')
            ->name('invoices.store');

        Route::post('/tagihan/bulanan', [SantriPaymentController::class, 'generateMonthlyInvoices'])
            ->middleware('permission:create pembayaran')
            ->name('invoices.monthly.generate');

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

        Route::get('/laporan/export', [SantriPaymentController::class, 'exportReports'])
            ->middleware('permission:view laporan keuangan')
            ->name('reports.export');
    });
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:view santri', 'throttle:120,1'])->group(function () {
    Route::get('/santri', [SantriManagementController::class, 'index'])->name('santri.index');
    Route::get('/santri/export', [SantriManagementController::class, 'export'])->name('santri.export');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage kamar', 'throttle:60,1'])->group(function () {
    Route::get('/santri/kamar', [RoomManagementController::class, 'index'])->name('rooms.index');
    Route::post('/santri/kamar', [RoomManagementController::class, 'store'])->name('rooms.store');
    Route::patch('/santri/kamar/{room}', [RoomManagementController::class, 'update'])->name('rooms.update');
    Route::post('/santri/kamar/{room}/santri', [RoomManagementController::class, 'assignSantris'])->name('rooms.santris.assign');
    Route::delete('/santri/kamar/{room}/santri/{santri}', [RoomManagementController::class, 'releaseSantri'])->name('rooms.santris.release');
    Route::delete('/santri/kamar/{room}', [RoomManagementController::class, 'destroy'])->name('rooms.destroy');
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

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Pengurus'])->group(function () {
    Route::get('/pengurus', [PengurusDashboardController::class, 'index'])->name('pengurus.dashboard');
    Route::redirect('/pengurus/santri', '/santri')->name('pengurus.santri');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'throttle:60,1'])
    ->prefix('pengurus/izin')
    ->name('pengurus.izin.')
    ->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index'])
            ->middleware('permission:create izin|approve izin')
            ->name('index');
        Route::post('/', [LeaveRequestController::class, 'store'])
            ->middleware('permission:create izin')
            ->name('store');
        Route::get('/{leaveRequest}/edit', [LeaveRequestController::class, 'edit'])
            ->middleware('permission:create izin')
            ->name('edit');
        Route::patch('/{leaveRequest}', [LeaveRequestController::class, 'update'])
            ->middleware('permission:create izin')
            ->name('update');
        Route::post('/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])
            ->middleware('permission:approve izin')
            ->name('approve');
        Route::post('/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])
            ->middleware('permission:approve izin')
            ->name('reject');
        Route::post('/{leaveRequest}/complete', [LeaveRequestController::class, 'complete'])
            ->middleware('permission:approve izin')
            ->name('complete');
        Route::delete('/{leaveRequest}', [LeaveRequestController::class, 'destroy'])
            ->middleware('permission:create izin')
            ->name('destroy');
    });

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage kamar|create izin|approve izin'])
    ->prefix('pengurus/laporan')
    ->name('pengurus.reports.')
    ->group(function () {
        Route::get('/', [OperationalReportController::class, 'index'])->name('index');
    });

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Musyrif/Ustadz'])->group(function () {
    Route::get('/musyrif', fn () => view('dashboard'))->name('musyrif.dashboard');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role:Bendahara'])->group(function () {
    Route::get('/bendahara', fn () => view('bendahara.dashboard'))->name('bendahara.dashboard');
    Route::get('/bendahara/laporan', fn () => view('bendahara.laporan'))->name('bendahara.laporan');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role_or_permission:Wali Santri|view portal wali', 'throttle:60,1'])->group(function () {
    Route::get('/wali-santri', [WaliSantriDashboardController::class, 'index'])->name('wali-santri.dashboard');
    Route::get('/wali-santri/tagihan/{invoice}', [WaliSantriDashboardController::class, 'showInvoice'])->name('wali-santri.invoices.show');
    Route::post('/wali-santri/tagihan/{invoice}/bukti-bayar', [WaliSantriDashboardController::class, 'storePaymentConfirmation'])->name('wali-santri.invoices.payment-confirmations.store');
    Route::get('/wali-santri/tagihan/{invoice}/kwitansi', [WaliSantriDashboardController::class, 'printInvoice'])->name('wali-santri.invoices.receipt');
});

require base_path('app/Modules/Auth/Routes/web.php');
require base_path('app/Modules/Saas/Routes/web.php');
