<?php

use App\Modules\Room\Controllers\RoomManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'permission:manage kamar', 'throttle:60,1'])->group(function () {
    Route::get('/santri/kamar', [RoomManagementController::class, 'index'])->name('rooms.index');
    Route::post('/santri/kamar', [RoomManagementController::class, 'store'])->name('rooms.store');
    Route::patch('/santri/kamar/{room}', [RoomManagementController::class, 'update'])->name('rooms.update');
    Route::post('/santri/kamar/{room}/santri', [RoomManagementController::class, 'assignSantris'])->name('rooms.santris.assign');
    Route::delete('/santri/kamar/{room}/santri/{santri}', [RoomManagementController::class, 'releaseSantri'])->name('rooms.santris.release');
    Route::delete('/santri/kamar/{room}', [RoomManagementController::class, 'destroy'])->name('rooms.destroy');
});
