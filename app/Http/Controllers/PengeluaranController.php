<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengeluaranController extends Controller
{
    /**
     * Halaman utama pengeluaran.
     */
    public function index()
    {
        $pengeluaran = Pengeluaran::with('karyawan')
            ->orderBy('tanggal', 'desc')
            ->orderBy('id_pengeluaran', 'desc')
            ->get();

        // ─── Stats ───
        $stats = [
            'total_bulan_ini' => Pengeluaran::bulanIni()->sum('jumlah'),
            'total_hari_ini'  => Pengeluaran::hariIni()->sum('jumlah'),
            'total_transaksi' => Pengeluaran::count(),
        ];

        // Kategori terbesar bulan ini
        $topKategori = Pengeluaran::bulanIni()
            ->select('kategori', DB::raw('SUM(jumlah) as total'))
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->first();

        $stats['kategori_terbesar']     = $topKategori->kategori ?? '-';
        $stats['kategori_terbesar_rp']  = $topKategori->total ?? 0;

        // ─── Breakdown per kategori (untuk side panel) ───
        $breakdown = Pengeluaran::bulanIni()
            ->select('kategori', DB::raw('SUM(jumlah) as total'), DB::raw('COUNT(*) as jumlah_transaksi'))
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get();

        $totalBulanIni = $stats['total_bulan_ini'] ?: 1;

        $kategoriList = ['Operasional', 'Gaji & Bonus', 'Perlengkapan', 'Lain-lain'];

        return view('pengeluaran.index', compact(
            'pengeluaran',
            'stats',
            'breakdown',
            'totalBulanIni',
            'kategoriList'
        ));
    }

    /**
     * Simpan pengeluaran baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal'          => ['required', 'date'],
            'kategori'         => ['required', 'in:Operasional,Gaji & Bonus,Perlengkapan,Lain-lain'],
            'nama_pengeluaran' => ['required', 'string', 'max:255'],
            'jumlah'           => ['required', 'numeric', 'min:1'],
            'sumber_dana'      => ['required', 'in:Kas Kecil Toko,Transfer Bank'],
        ], [
            'tanggal.required'          => 'Tanggal wajib diisi.',
            'kategori.required'         => 'Kategori wajib dipilih.',
            'kategori.in'               => 'Kategori tidak valid.',
            'nama_pengeluaran.required' => 'Keterangan wajib diisi.',
            'jumlah.required'           => 'Nominal wajib diisi.',
            'jumlah.min'                => 'Nominal harus lebih dari 0.',
            'sumber_dana.required'      => 'Sumber dana wajib dipilih.',
        ]);

        $validated['id_karyawan'] = auth()->user()->id_karyawan;

        Pengeluaran::create($validated);

        return redirect()
            ->route('pengeluaran.index')
            ->with('success', "Pengeluaran \"{$validated['nama_pengeluaran']}\" berhasil dicatat!");
    }

    /**
     * Hapus pengeluaran.
     */
    public function destroy($id)
    {
        $pengeluaran = Pengeluaran::findOrFail($id);
        $nama = $pengeluaran->nama_pengeluaran;

        $pengeluaran->delete();

        return redirect()
            ->route('pengeluaran.index')
            ->with('success', "Pengeluaran \"{$nama}\" berhasil dihapus!");
    }
}