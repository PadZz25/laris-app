<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// Halaman Publik (belum login)
// ─────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// ─────────────────────────────────────────────
// Halaman yang butuh login
// ─────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});