<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukController; 
use App\Http\Controllers\KategoriController;
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
    Route::resource('produk', ProdukController::class);
    Route::prefix('kategori')->name('kategori.')->controller(KategoriController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});