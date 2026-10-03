<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukController; 
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\KasbonController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\LaporanController;
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

    // Modul Buku Kasbon
    Route::prefix('kasbon')->name('kasbon.')->controller(KasbonController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/detail', 'detail')->name('detail');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::post('/{id}/bayar', 'bayar')->name('bayar');
    });

    // Modul Pasokan Supplier
    Route::prefix('supplier')->name('supplier.')->controller(SupplierController::class)->group(function () {
        Route::get('/', 'index')->name('index');

        // Supplier CRUD
        Route::post('/tambah', 'storeSupplier')->name('storeSupplier');
        Route::delete('/{id}/hapus', 'destroySupplier')->name('destroySupplier');

        // Pasokan
        Route::post('/pasokan', 'storePasokan')->name('storePasokan');
        Route::get('/pasokan/{id}/detail', 'detail')->name('detail');
        Route::delete('/pasokan/{id}', 'destroyFaktur')->name('destroyFaktur');
        Route::post('/pasokan/{id}/bayar', 'bayarFaktur')->name('bayarFaktur');
        Route::post('/produk-baru', 'storeProdukBaru')->name('storeProdukBaru');
    });

        Route::prefix('laporan')->name('laporan.')->controller(LaporanController::class)->group(function () {
            Route::get('/', 'index')->name('index');
    });

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});