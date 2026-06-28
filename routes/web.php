<?php

use App\Http\Controllers\DataExportDownloadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionStatusController;
use App\Http\Controllers\TenantImpersonationController;
use App\Modules\Auth\Actions\DetermineDashboardRouteAction;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'index'])->name('landing');
Route::get('/tentang',           [PublicController::class, 'about'])->name('about');
Route::get('/faq',               [PublicController::class, 'faq'])->name('faq');
Route::get('/syarat-ketentuan',  [PublicController::class, 'terms'])->name('terms');
Route::get('/keamanan-privasi',  [PublicController::class, 'securityPrivacy'])->name('security-privacy');

Route::get('/dashboard', function (DetermineDashboardRouteAction $determineDashboardRoute) {
    return redirect($determineDashboardRoute->handle(auth()->user()));
})->middleware(['auth', 'password_change_required'])->name('dashboard');

Route::get('/dashboard/home', function () {
    return view('dashboard');
})->middleware(['auth', 'password_change_required', 'subscription_active'])->name('dashboard.home');

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/subscription/expired', [SubscriptionStatusController::class, 'showExpired'])->name('subscription.expired');
    Route::post('/impersonation/stop', [TenantImpersonationController::class, 'destroy'])->name('impersonation.stop');
});

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'throttle:60,1'])->group(function () {
    Route::get('/exports/{dataExport}/download', DataExportDownloadController::class)->name('exports.download');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
});

require base_path('app/Modules/Auth/Routes/web.php');
require base_path('app/Modules/Saas/Routes/web.php');
require base_path('app/Modules/Tahfidz/Routes/web.php');
require base_path('app/Modules/Pelanggaran/Routes/web.php');
require base_path('app/Modules/Komunikasi/Routes/web.php');
require base_path('app/Modules/Akademik/Routes/web.php');
require base_path('app/Modules/Branding/Routes/web.php');
require base_path('app/Modules/Admin/Routes/web.php');
require base_path('app/Modules/Attendance/Routes/web.php');
require base_path('app/Modules/Payment/Routes/web.php');
require base_path('app/Modules/Room/Routes/web.php');
require base_path('app/Modules/Santri/Routes/web.php');
require base_path('app/Modules/LeaveRequest/Routes/web.php');
require base_path('app/Modules/Pengurus/Routes/web.php');
require base_path('app/Modules/Musyrif/Routes/web.php');
require base_path('app/Modules/Bendahara/Routes/web.php');
require base_path('app/Modules/WaliSantri/Routes/web.php');
require base_path('app/Modules/KesehatanQu/Routes/web.php');
require base_path('app/Modules/Backup/Routes/web.php');
require base_path('app/Modules/KeuanganQu/Routes/web.php');
require base_path('app/Modules/InventarisQu/Routes/web.php');
require base_path('app/Modules/KegiatanQu/Routes/web.php');
require base_path('app/Modules/PpdbQu/Routes/web.php');
require base_path('app/Modules/KepengurusanQu/Routes/web.php');
require base_path('app/Modules/KitabQu/Routes/web.php');
require base_path('app/Modules/PerpustakaanQu/Routes/web.php');
