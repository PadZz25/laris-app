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
        $validated = $request->validate([
            'barcode'       => ['required', 'string', 'max:255', 'unique:produk,barcode'],
            'nama_produk'   => ['required', 'string', 'max:255'],
            'id_kategori'   => ['required', 'exists:kategori_produk,id_kategori'],
            'satuan'        => ['required', 'string', 'max:50'],
            'harga_beli'    => ['required', 'numeric', 'min:0'],
            'harga_jual'    => ['required', 'numeric', 'min:0', 'gte:harga_beli'],
            'stok_sekarang' => ['required', 'integer', 'min:0'],
            'min_stok'      => ['required', 'integer', 'min:0'],
        ], [
            'barcode.required'       => 'Kode barcode wajib diisi.',
            'barcode.unique'         => 'Kode barcode sudah dipakai produk lain.',
            'nama_produk.required'   => 'Nama produk wajib diisi.',
            'id_kategori.required'   => 'Kategori wajib dipilih.',
            'id_kategori.exists'     => 'Kategori tidak valid.',
            'satuan.required'        => 'Satuan wajib diisi.',
            'harga_beli.required'    => 'Harga beli wajib diisi.',
            'harga_beli.numeric'     => 'Harga beli harus berupa angka.',
            'harga_jual.required'    => 'Harga jual wajib diisi.',
            'harga_jual.gte'         => 'Harga jual tidak boleh lebih rendah dari harga beli.',
            'stok_sekarang.required' => 'Stok awal wajib diisi.',
            'stok_sekarang.integer'  => 'Stok awal harus berupa angka bulat.',
            'min_stok.required'      => 'Batas minimal stok wajib diisi.',
        ]);

        Produk::create($validated);

        return redirect()
            ->route('produk.index')
            ->with('success', "Produk \"{$validated['nama_produk']}\" berhasil ditambahkan!");
    }
}   