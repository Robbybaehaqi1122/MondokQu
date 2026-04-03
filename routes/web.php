<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Modules\Auth\Actions\DetermineDashboardRouteAction;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (DetermineDashboardRouteAction $determineDashboardRoute) {
    return redirect($determineDashboardRoute->handle(auth()->user()));
})->middleware('auth')->name('dashboard');

Route::get('/dashboard/home', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard.home');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Role-based access routes
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/admin', fn () => view('admin.dashboard'))->name('admin.dashboard');
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::patch('/admin/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.update-role');
    Route::patch('/admin/users/{user}/password', [UserManagementController::class, 'updatePassword'])->name('admin.users.update-password');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
});

Route::middleware(['auth', 'role:Pengurus'])->group(function () {
    Route::get('/pengurus', fn () => view('pengurus.dashboard'))->name('pengurus.dashboard');
    Route::get('/pengurus/santri', fn () => view('pengurus.santri'))->name('pengurus.santri');
});

Route::middleware(['auth', 'role:Bendahara'])->group(function () {
    Route::get('/bendahara', fn () => view('bendahara.dashboard'))->name('bendahara.dashboard');
    Route::get('/bendahara/laporan', fn () => view('bendahara.laporan'))->name('bendahara.laporan');
});

require base_path('app/Modules/Auth/Routes/web.php');
require __DIR__.'/auth.php';
