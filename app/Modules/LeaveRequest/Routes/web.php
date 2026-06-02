<?php

use App\Modules\LeaveRequest\Controllers\LeaveRequestController;
use Illuminate\Support\Facades\Route;

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
