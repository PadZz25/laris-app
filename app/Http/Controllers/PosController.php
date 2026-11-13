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
     * Simpan transaksi penjualan secara atomik dengan pra-validasi stok
     * dan kompensasi rollback jika sp_tambah_item gagal di tengah loop.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.id'      => 'required|integer|exists:produk,id_produk',
            'items.*.qty'     => 'required|integer|min:1',
            'metode_bayar'    => 'required|in:Tunai,QRIS,Kasbon',
            'id_pelanggan'    => 'required_if:metode_bayar,Kasbon|nullable|exists:pelanggan,id_pelanggan',
            'uang_diterima'   => 'nullable|numeric|min:0',
            'kembalian'       => 'nullable|numeric',
        ], [
            'id_pelanggan.required_if' => 'Pilih nama warga untuk metode pembayaran Kasbon.',
            'items.*.qty.integer'      => 'Jumlah item harus berupa bilangan bulat minimal 1.',
        ]);

        // Gabungkan item duplikat per id_produk agar total qty tervalidasi akurat
        $aggregatedItems = [];
        foreach ($request->items as $item) {
            $idProduk = (int) $item['id'];
            $aggregatedItems[$idProduk] = ($aggregatedItems[$idProduk] ?? 0) + (int) $item['qty'];
        }

        $produkMap = Produk::whereIn('id_produk', array_keys($aggregatedItems))
            ->get()
            ->keyBy('id_produk');

        $totalServer = 0.0;
        foreach ($aggregatedItems as $idProduk => $qty) {
            $produk = $produkMap->get($idProduk);
            if (! $produk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan di database.'
                ], 422);
            }

            if ((int) $produk->stok_sekarang < $qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok \"{$produk->nama_produk}\" tidak mencukupi (tersedia: {$produk->stok_sekarang})."
                ], 422);
            }

            $totalServer += (float) $produk->harga_jual * $qty;
        }

        $metodeDb = 'tunai';
        $statusBayar = 'lunas';
        $idPelanggan = null;
        $uangDiterima = 0.0;
        $kembalian = 0.0;

        if ($request->metode_bayar === 'Tunai') {
            $uangDiterima = (float) ($request->uang_diterima ?? 0);
            if ($uangDiterima < $totalServer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Uang yang dibayarkan kurang dari total tagihan!'
                ], 422);
            }
            $kembalian = $uangDiterima - $totalServer;
        } elseif ($request->metode_bayar === 'QRIS') {
            $metodeDb = 'transfer';
        } elseif ($request->metode_bayar === 'Kasbon') {
            $metodeDb = 'hutang';
            $statusBayar = 'belum_lunas';
            $idPelanggan = (int) $request->id_pelanggan;
        }

        do {
            $nomorNota = 'INV-' . date('Ymd') . '-' . random_int(1000, 9999);
        } while (Penjualan::where('nomor_nota', $nomorNota)->exists());

        $penjualan = null;

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
                'uang_diterima' => $uangDiterima,
                'kembalian'     => $kembalian,
            ]);

            $idPenjualan = $penjualan->id_penjualan;

            // 2. Loop Item dan panggil Stored Procedure
            // SP ini otomatis mengurangi stok & menambah total_belanja/akhir
            foreach ($aggregatedItems as $idProduk => $qty) {
                DB::statement('CALL sp_tambah_item(?, ?, ?)', [
                    $idPenjualan,
                    $idProduk,
                    $qty
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan!',
                'id_penjualan' => $idPenjualan
            ]);

        } catch (\Throwable $e) {
            // Hapus detail lebih dulu agar trigger trg_setelah_hapus_item memulihkan stok & kasbon
            if ($penjualan) {
                try {
                    DetailPenjualan::where('id_penjualan', $penjualan->id_penjualan)->delete();
                    $penjualan->delete();
                } catch (\Throwable $rollbackException) {
                    report($rollbackException);
                }
            }

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