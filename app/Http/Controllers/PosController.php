<?php

namespace App\Http\Controllers;

use App\Models\KategoriProduk;
use App\Models\Pelanggan;
use App\Models\Produk;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        // ... (biarkan kode index yang sudah ada) ...
        $produk = Produk::with('kategori')->where('stok_sekarang', '>', 0)->orderBy('nama_produk')->get();
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

        return view('kasir.index', compact('produkData', 'kategoriList', 'pelangganList'));
    }

    /**
     * Simpan transaksi penjualan.
     */
        public function store(Request $request)
    {
        $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.id'      => 'required|exists:produk,id_produk',
            'items.*.qty'     => 'required|numeric|min:1',
            'metode_bayar'    => 'required|in:Tunai,QRIS,Kasbon',
            'id_pelanggan'    => 'nullable|exists:pelanggan,id_pelanggan',
            'uang_diterima'   => 'nullable|numeric',
            'kembalian'       => 'nullable|numeric',
        ]);

        $nomorNota = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);

        $metodeDb = 'tunai';
        $statusBayar = 'lunas';
        $idPelanggan = null;

        if ($request->metode_bayar === 'QRIS') {
            $metodeDb = 'transfer';
        } elseif ($request->metode_bayar === 'Kasbon') {
            $metodeDb = 'hutang';
            $statusBayar = 'belum_lunas';
            $idPelanggan = $request->id_pelanggan;
        }

        try {
            // 1. Buat Header Penjualan
            $penjualan = Penjualan::create([
                'nomor_nota'    => $nomorNota,
                'id_karyawan'   => auth()->user()->id_karyawan,
                'id_pelanggan'  => $idPelanggan,
                'tanggal'       => now(),
                'total_belanja' => 0,
                'diskon'        => 0,
                'total_akhir'   => 0,
                'metode_bayar'  => $metodeDb,
                'status_bayar'  => $statusBayar,
                'uang_diterima' => $request->uang_diterima ?? 0,
                'kembalian'     => $request->kembalian ?? 0,
            ]);

            $idPenjualan = $penjualan->id_penjualan;

            // 2. Loop Item dan panggil Stored Procedure
            // SP ini otomatis mengurangi stok & menambah total_belanja/akhir
            foreach ($request->items as $item) {
                DB::statement('CALL sp_tambah_item(?, ?, ?)', [
                    $idPenjualan,
                    $item['id'],
                    $item['qty']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan!',
                'id_penjualan' => $idPenjualan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Halaman cetak nota.
     */
    public function printNota($id)
    {
        $penjualan = Penjualan::with(['detail.produk', 'pelanggan', 'karyawan'])->findOrFail($id);
        return view('kasir.nota', compact('penjualan'));
    }
}