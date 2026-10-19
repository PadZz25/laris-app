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

        // Siapkan data JSON untuk JS
        $produkData = $produk->map(function ($p) {
            return [
                'id'       => $p->id_produk,
                'nama'     => $p->nama_produk,
                'barcode'  => $p->barcode,
                'kategori' => $p->kategori->nama_kategori ?? 'Tanpa Kategori',
                'harga'    => (float) $p->harga_jual,
                'stok'     => (int) $p->stok_sekarang,
                'satuan'   => $p->satuan,
                'foto'     => $p->foto_produk ? asset('storage/' . $p->foto_produk) : null,
            ];
        })->values()->toArray();

        $kategoriList  = KategoriProduk::orderBy('nama_kategori')->get();
        $pelangganList = Pelanggan::orderBy('nama_pelanggan')->get();

        return view('kasir.index', compact(
            'produkData',
            'kategoriList',
            'pelangganList'
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