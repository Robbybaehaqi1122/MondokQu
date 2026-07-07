<?php

use App\Modules\PengaturanQu\Controllers\BlogController;
use App\Modules\PengaturanQu\Controllers\PengaturanQuDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role_or_permission:Superadmin|Admin|manage pengaturan'])
    ->prefix('pengaturan')
    ->name('pengaturan.')
    ->group(function () {
        Route::get('/', [PengaturanQuDashboardController::class, 'index'])->name('dashboard');

        Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
        Route::get('/blog/create', [BlogController::class, 'create'])->name('blog.create');
        Route::post('/blog', [BlogController::class, 'store'])->name('blog.store');
        Route::get('/blog/{blog}/edit', [BlogController::class, 'edit'])->name('blog.edit');
        Route::put('/blog/{blog}', [BlogController::class, 'update'])->name('blog.update');
        Route::delete('/blog/{blog}', [BlogController::class, 'destroy'])->name('blog.destroy');
    });
