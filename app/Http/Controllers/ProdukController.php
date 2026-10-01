<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    /**
     * Halaman index: daftar semua produk + stats.
     */
    public function index()
    {
            // Load semua produk + relasi kategori (client-side filter)
        $produk = Produk::with('kategori')->orderBy('id_produk')->get();
        $kategoriList = KategoriProduk::orderBy('nama_kategori')->get();

        // Stats untuk kartu ringkasan
        $stats = [
            'total_jenis'  => Produk::count(),
            'stok_menipis' => Produk::stokMenipis()->count(),
            'stok_habis'   => Produk::stokHabis()->count(),
            'total_aset'   => Produk::selectRaw('SUM(stok_sekarang * harga_beli) as total')->value('total') ?? 0,
        ];

        return view('produk.index', compact('produk', 'stats', 'kategoriList'));
    }

    /**
     * Simpan produk baru (akan diimplementasikan di Sub-Fase 1.3).
     */
    public function store(Request $request)
    {
        return redirect()->route('produk.index')
            ->with('success', 'Fitur tambah produk belum diimplementasi.');
    }

    /**
     * Update produk (akan diimplementasikan di Sub-Fase 1.4).
     */
    public function update(Request $request, $id)
    {
        return redirect()->route('produk.index')
            ->with('success', 'Fitur edit produk belum diimplementasi.');
    }

    /**
     * Hapus produk (akan diimplementasikan di Sub-Fase 1.4).
     */
    public function destroy($id)
    {
        return redirect()->route('produk.index')
            ->with('success', 'Fitur hapus produk belum diimplementasi.');
    }
}