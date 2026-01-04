<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController;

// Halaman Welcome
Route::get('/', function () {
    return view('welcome');
});

// Route Dashboard (Sudah dilindungi Auth dan Verified)
Route::middleware(['auth', 'verified'])->group(function () {
    // Kita arahkan /dashboard ke Controller Admin kita
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ADMIN USER MANAGEMENT
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::patch('/admin/users/{id}/approve', [AdminUserController::class, 'approveKyc'])->name('admin.users.approve');
    Route::patch('/admin/users/{id}/reject', [AdminUserController::class, 'rejectKyc'])->name('admin.users.reject');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
