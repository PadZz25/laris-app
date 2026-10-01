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
    public function index(Request $request)
    {
        // ─────────────────────────────────────────────
        // Query dasar produk + relasi kategori
        // ─────────────────────────────────────────────
        $query = Produk::with('kategori');

        // Filter by search (nama produk / barcode)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by kategori
        if ($kategoriId = $request->input('kategori')) {
            $query->where('id_kategori', $kategoriId);
        }

        $produk = $query->orderBy('id_produk', 'asc')->get();

        // ─────────────────────────────────────────────
        // Stats untuk kartu ringkasan di atas tabel
        // ─────────────────────────────────────────────
        $stats = [
            'total_jenis'    => Produk::count(),
            'stok_menipis'   => Produk::stokMenipis()->count(),
            'stok_habis'     => Produk::stokHabis()->count(),
            'total_aset'     => Produk::selectRaw('SUM(stok_sekarang * harga_beli) as total')->value('total') ?? 0,
        ];

        // Semua kategori untuk filter dropdown / pill
        $kategoriList = KategoriProduk::orderBy('nama_kategori')->get();

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