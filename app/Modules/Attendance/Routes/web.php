<?php

use App\Modules\Attendance\Controllers\AttendanceActivityController;
use App\Modules\Attendance\Controllers\AttendanceDashboardController;
use App\Modules\Attendance\Controllers\AttendanceRecordController;
use App\Modules\Attendance\Controllers\AttendanceReportController;
use App\Modules\Attendance\Controllers\AttendanceSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage absensi', 'throttle:60,1'])->group(function () {
    Route::prefix('absen')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceDashboardController::class, 'index'])->name('dashboard');

        Route::get('/kegiatan', [AttendanceActivityController::class, 'index'])->name('activities.index');
        Route::post('/kegiatan', [AttendanceActivityController::class, 'store'])->name('activities.store');
        Route::patch('/kegiatan/{attendanceActivity}', [AttendanceActivityController::class, 'update'])->name('activities.update');
        Route::delete('/kegiatan/{attendanceActivity}', [AttendanceActivityController::class, 'destroy'])->name('activities.destroy');

        Route::get('/laporan', [AttendanceReportController::class, 'index'])->name('reports.index');
        Route::get('/laporan/pdf', [AttendanceReportController::class, 'exportPdf'])->name('reports.pdf');

        Route::get('/sesi', [AttendanceSessionController::class, 'index'])->name('sessions.index');
        Route::post('/sesi', [AttendanceSessionController::class, 'store'])->name('sessions.store');
        Route::get('/sesi/{attendanceSession}/input', [AttendanceRecordController::class, 'edit'])->name('sessions.records.edit');
        Route::put('/sesi/{attendanceSession}/input', [AttendanceRecordController::class, 'update'])->name('sessions.records.update');
        Route::patch('/sesi/{attendanceSession}', [AttendanceSessionController::class, 'update'])->name('sessions.update');
        Route::delete('/sesi/{attendanceSession}', [AttendanceSessionController::class, 'destroy'])->name('sessions.destroy');
    });
});
