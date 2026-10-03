<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukController; 
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PengeluaranController;
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
    // Penyesuaian Stok (Stock Opname)
    Route::post('/produk/penyesuaian', [ProdukController::class, 'penyesuaianStok'])
        ->name('produk.penyesuaian');

    Route::resource('produk', ProdukController::class);
    Route::resource('produk', ProdukController::class);
    Route::prefix('kategori')->name('kategori.')->controller(KategoriController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    Route::prefix('pengeluaran')->name('pengeluaran.')->controller(PengeluaranController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});