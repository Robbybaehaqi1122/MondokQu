<?php

use App\Modules\PengaturanQu\Controllers\BlogCategoryController;
use App\Modules\PengaturanQu\Controllers\BlogController;
use App\Modules\PengaturanQu\Controllers\PengaturanQuDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password_change_required', 'subscription_active', 'verified', 'role_or_permission:Superadmin|manage pengaturan'])
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

        Route::get('/blog-categories', [BlogCategoryController::class, 'index'])->name('blog-category.index');
        Route::get('/blog-categories/create', [BlogCategoryController::class, 'create'])->name('blog-category.create');
        Route::post('/blog-categories', [BlogCategoryController::class, 'store'])->name('blog-category.store');
        Route::get('/blog-categories/{blogCategory}/edit', [BlogCategoryController::class, 'edit'])->name('blog-category.edit');
        Route::put('/blog-categories/{blogCategory}', [BlogCategoryController::class, 'update'])->name('blog-category.update');
        Route::delete('/blog-categories/{blogCategory}', [BlogCategoryController::class, 'destroy'])->name('blog-category.destroy');
    });
