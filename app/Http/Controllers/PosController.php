<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\Pelanggan;
use App\Models\Produk;
use Illuminate\Http\Request;

class PosController extends Controller
{
    /**
     * Halaman utama kasir.
     */
    public function index()
    {
        $produk = Produk::with('kategori')
            ->where('stok_sekarang', '>', 0)
            ->orderBy('nama_produk')
            ->get();

        $kategoriList = KategoriProduk::orderBy('nama_kategori')->get();
        $pelangganList = Pelanggan::orderBy('nama_pelanggan')->get();

        // Kategori terlaris (bulan ini) — placeholder dulu
        // Akan dioptimasi di Sub-Fase 5.7
        $topCategories = collect();

        return view('kasir.index', compact(
            'produk',
            'kategoriList',
            'pelangganList',
            'topCategories'
        ));
    }

    /**
     * Simpan transaksi penjualan (akan diimplementasi di Sub-Fase 5.6).
     */
    public function store(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Fitur transaksi akan diimplementasi di Sub-Fase 5.6.',
        ], 501);
    }
}