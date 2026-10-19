<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProdukController; 
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\KasbonController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// Halaman Publik
// ─────────────────────────────────────────────
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// Smart Redirect untuk halaman utama
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->peran === 'admin' 
            ? redirect()->route('laporan.index') 
            : redirect()->route('kasir.index');
    }
    return redirect()->route('login');
});

// ─────────────────────────────────────────────
// Halaman yang butuh login
// ─────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    
    // ==========================================
    // AKSES UNTUK SEMUA (Admin & Kasir)
    // ==========================================
    
    // Modul Kasir / POS
    Route::prefix('kasir')->name('kasir.')->controller(PosController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/store', 'store')->name('store');
        Route::get('/nota/{id}', 'printNota')->name('nota');
    });

    // Modul Buku Kasbon
    Route::prefix('kasbon')->name('kasbon.')->controller(KasbonController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}/detail', 'detail')->name('detail');
        Route::post('/{id}/bayar', 'bayar')->name('bayar');
    });

    // Modul Produk (Read Only untuk Kasir)
    Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
    Route::get('/kategori', [KategoriController::class, 'index'])->name('kategori.index');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


    // ==========================================
    // AKSES KHUSUS ADMIN (Ina)
    // ==========================================
    Route::middleware('role:admin')->group(function () {
        
        // Laporan & Omzet
        Route::prefix('laporan')->name('laporan.')->controller(LaporanController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/export-excel', 'exportExcel')->name('exportExcel');
            Route::get('/export-pdf', 'exportPdf')->name('exportPdf');
        });

        // Modul Pasokan Supplier
        Route::prefix('supplier')->name('supplier.')->controller(SupplierController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/tambah', 'storeSupplier')->name('storeSupplier');
            Route::delete('/{id}/hapus', 'destroySupplier')->name('destroySupplier');
            Route::post('/pasokan', 'storePasokan')->name('storePasokan');
            Route::get('/pasokan/{id}/detail', 'detail')->name('detail');
            Route::delete('/pasokan/{id}', 'destroyFaktur')->name('destroyFaktur');
            Route::post('/pasokan/{id}/bayar', 'bayarFaktur')->name('bayarFaktur');
            Route::post('/produk-baru', 'storeProdukBaru')->name('storeProdukBaru');
        });

        // Modul Pengeluaran
        Route::prefix('pengeluaran')->name('pengeluaran.')->controller(PengeluaranController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });

        // Modifikasi Produk & Kategori (Admin)
        Route::post('/produk', [ProdukController::class, 'store'])->name('produk.store');
        Route::put('/produk/{id}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('/produk/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');
        Route::post('/produk/penyesuaian', [ProdukController::class, 'penyesuaianStok'])->name('produk.penyesuaian');

        Route::post('/kategori', [KategoriController::class, 'store'])->name('kategori.store');
        Route::put('/kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
        Route::delete('/kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');

        // Hapus Warga Kasbon
        Route::delete('/kasbon/{id}', [KasbonController::class, 'destroy'])->name('kasbon.destroy');
    });
});