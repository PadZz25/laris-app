<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        // ─────────────────────────────────────────────
        // Validasi
        // ─────────────────────────────────────────────
        $validated = $request->validate([
            'barcode'       => ['required', 'string', 'max:255', 'unique:produk,barcode'],
            'nama_produk'   => ['required', 'string', 'max:255'],
            'satuan'        => ['required', 'string', 'max:50'],
            'harga_beli'    => ['required', 'numeric', 'min:0'],
            'harga_jual'    => ['required', 'numeric', 'min:0', 'gte:harga_beli'],
            'stok_sekarang' => ['required', 'integer', 'min:0'],
            'min_stok'      => ['required', 'integer', 'min:0'],
            'foto_produk'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Kategori: salah satu harus diisi
            'id_kategori'   => ['nullable', 'required_without:new_kategori', 'exists:kategori_produk,id_kategori'],
            'new_kategori'  => ['nullable', 'required_without:id_kategori', 'string', 'max:255', 'unique:kategori_produk,nama_kategori'],
        ], [
            'barcode.required'         => 'Kode barcode wajib diisi.',
            'barcode.unique'           => 'Kode barcode sudah dipakai produk lain.',
            'nama_produk.required'     => 'Nama produk wajib diisi.',
            'satuan.required'          => 'Satuan wajib diisi.',
            'harga_beli.required'      => 'Harga beli wajib diisi.',
            'harga_jual.required'      => 'Harga jual wajib diisi.',
            'harga_jual.gte'           => 'Harga jual tidak boleh lebih rendah dari harga beli.',
            'stok_sekarang.required'   => 'Stok awal wajib diisi.',
            'min_stok.required'        => 'Batas minimal stok wajib diisi.',
            'foto_produk.image'        => 'File harus berupa gambar.',
            'foto_produk.mimes'        => 'Format foto harus jpg, jpeg, png, atau webp.',
            'foto_produk.max'          => 'Ukuran foto maksimal 2MB.',

            'id_kategori.required_without'  => 'Pilih kategori atau isi kategori baru.',
            'id_kategori.exists'            => 'Kategori yang dipilih tidak valid.',
            'new_kategori.required_without' => 'Pilih kategori atau isi kategori baru.',
            'new_kategori.unique'           => 'Nama kategori sudah ada. Pilih dari dropdown saja.',
        ]);

        // ─────────────────────────────────────────────
        // Handle upload foto
        // ─────────────────────────────────────────────
        if ($request->hasFile('foto_produk')) {
            $validated['foto_produk'] = $request->file('foto_produk')
                ->store('produk', 'public');
        }

        // ─────────────────────────────────────────────
        // Handle kategori baru (kalau diisi)
        // ─────────────────────────────────────────────
        if (!empty($validated['new_kategori'])) {
            $kategoriBaru = KategoriProduk::create([
                'nama_kategori' => $validated['new_kategori'],
            ]);
            $validated['id_kategori'] = $kategoriBaru->id_kategori;
        }

        // Hapus key yang tidak perlu dimasukkan ke tabel produk
        unset($validated['new_kategori']);

        // ─────────────────────────────────────────────
        // Simpan produk
        // ─────────────────────────────────────────────
        Produk::create($validated);

        return redirect()
            ->route('produk.index')
            ->with('success', "Produk \"{$validated['nama_produk']}\" berhasil ditambahkan!");
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $validated = $request->validate([
            'barcode'       => ['required', 'string', 'max:255', Rule::unique('produk', 'barcode')->ignore($produk->id_produk, 'id_produk')],
            'nama_produk'   => ['required', 'string', 'max:255'],
            'satuan'        => ['required', 'string', 'max:50'],
            'harga_beli'    => ['required', 'numeric', 'min:0'],
            'harga_jual'    => ['required', 'numeric', 'min:0', 'gte:harga_beli'],
            'stok_sekarang' => ['required', 'integer', 'min:0'],
            'min_stok'      => ['required', 'integer', 'min:0'],
            'foto_produk'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'id_kategori'   => ['nullable', 'required_without:new_kategori', 'exists:kategori_produk,id_kategori'],
            'new_kategori'  => ['nullable', 'required_without:id_kategori', 'string', 'max:255', 'unique:kategori_produk,nama_kategori'],
        ], [
            'barcode.required'              => 'Kode barcode wajib diisi.',
            'barcode.unique'                => 'Kode barcode sudah dipakai produk lain.',
            'nama_produk.required'          => 'Nama produk wajib diisi.',
            'satuan.required'               => 'Satuan wajib diisi.',
            'harga_beli.required'           => 'Harga beli wajib diisi.',
            'harga_jual.required'           => 'Harga jual wajib diisi.',
            'harga_jual.gte'                => 'Harga jual tidak boleh lebih rendah dari harga beli.',
            'stok_sekarang.required'        => 'Stok wajib diisi.',
            'min_stok.required'             => 'Batas minimal stok wajib diisi.',
            'foto_produk.image'             => 'File harus berupa gambar.',
            'foto_produk.mimes'             => 'Format foto harus jpg, jpeg, png, atau webp.',
            'foto_produk.max'               => 'Ukuran foto maksimal 2MB.',
            'id_kategori.required_without'  => 'Pilih kategori atau isi kategori baru.',
            'new_kategori.required_without' => 'Pilih kategori atau isi kategori baru.',
            'new_kategori.unique'           => 'Nama kategori sudah ada. Pilih dari dropdown saja.',
        ]);

        // Handle kategori baru
        if (!empty($validated['new_kategori'])) {
            $kategoriBaru = KategoriProduk::create([
                'nama_kategori' => $validated['new_kategori'],
            ]);
            $validated['id_kategori'] = $kategoriBaru->id_kategori;
        }
        unset($validated['new_kategori']);

        // Handle upload foto baru — kalau ada, hapus yang lama
        if ($request->hasFile('foto_produk')) {
            if ($produk->foto_produk && Storage::disk('public')->exists($produk->foto_produk)) {
                Storage::disk('public')->delete($produk->foto_produk);
            }
            $validated['foto_produk'] = $this->uploadFotoProduk($request->file('foto_produk'));
        }

        $produk->update($validated);

        return redirect()
            ->route('produk.index')
            ->with('success', "Produk \"{$produk->nama_produk}\" berhasil diupdate!");
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $nama = $produk->nama_produk;

        // Cek apakah produk pernah muncul di transaksi penjualan
        if ($produk->detailPenjualan()->exists()) {
            return redirect()
                ->route('produk.index')
                ->with('error', "Produk \"{$nama}\" tidak bisa dihapus karena sudah pernah terjual. Ubah stok atau biarkan saja.");
        }

        // Hapus foto dari storage
        if ($produk->foto_produk && Storage::disk('public')->exists($produk->foto_produk)) {
            Storage::disk('public')->delete($produk->foto_produk);
        }

        $produk->delete();

        return redirect()
            ->route('produk.index')
            ->with('success', "Produk \"{$nama}\" berhasil dihapus!");
    }
}   