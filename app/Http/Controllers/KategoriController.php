<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    /**
     * Return daftar kategori (JSON) — untuk AJAX refresh.
     */
    public function index()
    {
        return response()->json($this->getKategoriData());
    }

    /**
     * Tambah kategori baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', 'unique:kategori_produk,nama_kategori'],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Nama kategori sudah ada.',
            'nama_kategori.max'      => 'Nama kategori maksimal 255 karakter.',
        ]);

        KategoriProduk::create($validated);

        return response()->json([
            'success'       => true,
            'message'       => "Kategori \"{$validated['nama_kategori']}\" berhasil ditambahkan!",
            'kategori_list' => $this->getKategoriData(),
        ]);
    }

    /**
     * Rename kategori.
     */
    public function update(Request $request, $id)
    {
        $kategori = KategoriProduk::findOrFail($id);

        $validated = $request->validate([
            'nama_kategori' => [
                'required', 'string', 'max:255',
                Rule::unique('kategori_produk', 'nama_kategori')->ignore($kategori->id_kategori, 'id_kategori'),
            ],
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.unique'   => 'Nama kategori sudah ada.',
        ]);

        $oldName = $kategori->nama_kategori;
        $kategori->update($validated);

        return response()->json([
            'success'       => true,
            'message'       => "Kategori \"{$oldName}\" berhasil diubah menjadi \"{$validated['nama_kategori']}\".",
            'kategori_list' => $this->getKategoriData(),
        ]);
    }

    /**
     * Hapus kategori (dengan proteksi kalau masih dipakai produk).
     */
    public function destroy($id)
    {
        $kategori = KategoriProduk::findOrFail($id);
        $jumlahProduk = $kategori->produk()->count();

        if ($jumlahProduk > 0) {
            return response()->json([
                'success' => false,
                'message' => "Kategori \"{$kategori->nama_kategori}\" tidak bisa dihapus karena masih dipakai oleh {$jumlahProduk} produk. Pindahkan atau hapus produknya dulu.",
            ], 422);
        }

        $nama = $kategori->nama_kategori;
        $kategori->delete();

        return response()->json([
            'success'       => true,
            'message'       => "Kategori \"{$nama}\" berhasil dihapus!",
            'kategori_list' => $this->getKategoriData(),
        ]);
    }

    /**
     * Helper: ambil data kategori + jumlah produk (untuk JSON response).
     */
    private function getKategoriData(): array
    {
        return KategoriProduk::withCount('produk')
            ->orderBy('nama_kategori')
            ->get()
            ->map(fn ($kat) => [
                'id_kategori'   => $kat->id_kategori,
                'nama_kategori' => $kat->nama_kategori,
                'jumlah_produk' => $kat->produk_count,
            ])
            ->toArray();
    }
}