<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\DetailPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\LaporanPenjualanExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        // Filter Periode (Default: 7 hari terakhir)
        $startDate = $request->input('start_date', Carbon::now()->subDays(6)->format('Y-m-d'));
        $endDate   = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        // 1. Hitung KPI Utama & Laba Kotor
        $totalOmzet = Penjualan::whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate])->sum('total_akhir');
        $uangMasuk = Penjualan::whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate])
            ->whereIn('metode_bayar', ['tunai', 'transfer'])->sum('total_akhir');
        $piutangBaru = Penjualan::whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate])
            ->where('metode_bayar', 'hutang')->sum('total_akhir');
        $stokMenipis = Produk::whereColumn('stok_sekarang', '<=', 'min_stok')->count();

        // Laba Kotor (Subtotal - HPP)
        $labaKotor = DetailPenjualan::join('penjualan', 'detail_penjualan.id_penjualan', '=', 'penjualan.id_penjualan')
            ->whereBetween(DB::raw('DATE(penjualan.tanggal)'), [$startDate, $endDate])
            ->sum(DB::raw('detail_penjualan.subtotal - (detail_penjualan.hpp_saat_itu * detail_penjualan.jumlah)'));

        // 2. Data untuk Grafik Tren Omzet Harian
        $trenOmzet = Penjualan::select(DB::raw('DATE(tanggal) as date'), DB::raw('SUM(total_akhir) as total'))
            ->whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate])
            ->groupBy('date')->orderBy('date')->get();
        
        $chartDates = []; $chartTotals = [];
        foreach ($trenOmzet as $t) {
            $chartDates[] = Carbon::parse($t->date)->format('d M');
            $chartTotals[] = (float) $t->total;
        }

        // 3. Data Grafik Kategori Terlaris
        $kategoriOmzet = DetailPenjualan::join('produk', 'detail_penjualan.id_produk', '=', 'produk.id_produk')
            ->join('kategori_produk', 'produk.id_kategori', '=', 'kategori_produk.id_kategori')
            ->join('penjualan', 'detail_penjualan.id_penjualan', '=', 'penjualan.id_penjualan')
            ->select('kategori_produk.nama_kategori', DB::raw('SUM(detail_penjualan.subtotal) as total'))
            ->whereBetween(DB::raw('DATE(penjualan.tanggal)'), [$startDate, $endDate])
            ->groupBy('kategori_produk.nama_kategori')->orderByDesc('total')->limit(5)->get();

        $chartKategoriLabels = $kategoriOmzet->pluck('nama_kategori')->toArray();
        $chartKategoriSeries = $kategoriOmzet->pluck('total')->map(fn($v) => (float)$v)->toArray();

        // 4. Data Grafik Metode Pembayaran
        $metodeBayar = Penjualan::select('metode_bayar', DB::raw('SUM(total_akhir) as total'))
            ->whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate])
            ->groupBy('metode_bayar')->get();
        
        $chartMetodeLabels = $metodeBayar->map(fn($m) => strtoupper($m->metode_bayar))->toArray();
        $chartMetodeSeries = $metodeBayar->pluck('total')->map(fn($v) => (float)$v)->toArray();

        // 5. Data Top 5 Produk Terlaris
        $topProduk = DetailPenjualan::join('produk', 'detail_penjualan.id_produk', '=', 'produk.id_produk')
            ->join('penjualan', 'detail_penjualan.id_penjualan', '=', 'penjualan.id_penjualan')
            ->select('produk.nama_produk', DB::raw('SUM(detail_penjualan.jumlah) as total_qty'))
            ->whereBetween(DB::raw('DATE(penjualan.tanggal)'), [$startDate, $endDate])
            ->groupBy('produk.id_produk', 'produk.nama_produk')->orderByDesc('total_qty')->limit(5)->get();

        // 6. Data Tabel
        $rekapPenjualan = Penjualan::with(['pelanggan', 'karyawan'])
            ->whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate])
            ->orderByDesc('tanggal')->limit(10)->get();

        $daftarStokMenipis = Produk::with('kategori')
            ->whereColumn('stok_sekarang', '<=', 'min_stok')
            ->orderBy('stok_sekarang')->limit(5)->get();

        return view('laporan.index', compact(
            'startDate', 'endDate', 'totalOmzet', 'uangMasuk', 'piutangBaru', 'stokMenipis', 'labaKotor',
            'chartDates', 'chartTotals', 'chartKategoriLabels', 'chartKategoriSeries',
            'chartMetodeLabels', 'chartMetodeSeries', 'topProduk',
            'rekapPenjualan', 'daftarStokMenipis'
        ));
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(6)->format('Y-m-d'));
        $endDate   = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        return Excel::download(new LaporanPenjualanExport($startDate, $endDate), 'Laporan_Penjualan_'.$startDate.'_sd_'.$endDate.'.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(6)->format('Y-m-d'));
        $endDate   = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $rekapPenjualan = Penjualan::with(['pelanggan', 'karyawan'])
            ->whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate])
            ->orderByDesc('tanggal')->get();

        $pdf = Pdf::loadView('laporan.pdf', compact('rekapPenjualan', 'startDate', 'endDate'));
        return $pdf->download('Laporan_Penjualan_'.$startDate.'_sd_'.$endDate.'.pdf');
    }
}