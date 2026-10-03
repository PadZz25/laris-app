<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasbonController extends Controller
{
    public function index()
    {
        $pelanggan = Pelanggan::orderByDesc('total_hutang')
            ->orderBy('nama_pelanggan')
            ->get();

        $stats = [
            'total_warga'   => Pelanggan::count(),
            'warga_kasbon'  => Pelanggan::where('total_hutang', '>', 0)->count(),
            'warga_bebas'   => Pelanggan::where('total_hutang', '<=', 0)->count(),
            'total_piutang' => Pelanggan::sum('total_hutang'),
        ];

        return view('kasbon.index', compact('pelanggan', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pelanggan' => ['required', 'string', 'max:255'],
            'no_telepon'     => ['nullable', 'string', 'max:30'],
            'alamat'         => ['nullable', 'string', 'max:255'],
            'kasbon_awal'    => ['nullable', 'numeric', 'min:0'],
        ], [
            'nama_pelanggan.required' => 'Nama warga wajib diisi.',
            'kasbon_awal.numeric'     => 'Kasbon awal harus berupa angka.',
        ]);

        $kasbonAwal = (float) ($validated['kasbon_awal'] ?? 0);

        try {
            DB::select('CALL sp_tambah_warga_kasbon(?, ?, ?, ?, ?)', [
                $validated['nama_pelanggan'],
                $validated['no_telepon'] ?? null,
                $validated['alamat'] ?? null,
                $kasbonAwal,
                auth()->user()->id_karyawan,
            ]);
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menyimpan warga: ' . $e->getMessage());
        }

        $msg = "Warga \"{$validated['nama_pelanggan']}\" berhasil ditambahkan!";
        if ($kasbonAwal > 0) {
            $msg .= " Kasbon awal Rp " . number_format($kasbonAwal, 0, ',', '.') . " tercatat.";
        }

        return redirect()->route('kasbon.index')->with('success', $msg);
    }

    public function destroy($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        if ($pelanggan->total_hutang > 0) {
            return redirect()->route('kasbon.index')
                ->with('error', "Warga \"{$pelanggan->nama_pelanggan}\" masih memiliki hutang Rp "
                    . number_format($pelanggan->total_hutang, 0, ',', '.')
                    . ". Tidak bisa dihapus.");
        }

        $nama = $pelanggan->nama_pelanggan;
        $pelanggan->delete();

        return redirect()->route('kasbon.index')
            ->with('success', "Warga \"{$nama}\" berhasil dihapus!");
    }

    public function detail($id)
    {
        $pelanggan = Pelanggan::findOrFail($id);

        $nota = Penjualan::with('detail.produk')
            ->where('id_pelanggan', $id)
            ->where('status_bayar', 'belum_lunas')
            ->orderByDesc('tanggal')
            ->get()
            ->map(fn ($n) => [
                'id_penjualan' => $n->id_penjualan,
                'nomor_nota'   => $n->nomor_nota,
                'tanggal'      => $n->tanggal->format('d/m/Y'),
                'total_akhir'  => (float) $n->total_akhir,
                'jumlah_item'  => $n->detail->count(),
            ]);

        return response()->json([
            'pelanggan' => [
                'id_pelanggan'   => $pelanggan->id_pelanggan,
                'nama_pelanggan' => $pelanggan->nama_pelanggan,
                'no_telepon'     => $pelanggan->no_telepon,
                'alamat'         => $pelanggan->alamat,
                'total_hutang'   => (float) $pelanggan->total_hutang,
            ],
            'nota' => $nota,
        ]);
    }

    public function bayar(Request $request, $id)
    {
        $validated = $request->validate([
            'nominal_bayar' => ['required', 'numeric', 'min:1'],
        ], [
            'nominal_bayar.required' => 'Nominal bayar wajib diisi.',
            'nominal_bayar.min'      => 'Nominal bayar harus lebih dari 0.',
        ]);

        $pelanggan = Pelanggan::findOrFail($id);
        $nama = $pelanggan->nama_pelanggan;

        try {
            $result = DB::select('CALL sp_bayar_kasbon_pelanggan(?, ?)', [
                $id,
                (float) $validated['nominal_bayar'],
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal proses pembayaran: ' . $e->getMessage());
        }

        $nominalDibayar = (float) ($result[0]->nominal_dibayar ?? 0);
        $sisaHutang     = (float) ($result[0]->sisa_hutang ?? 0);
        $jenis          = $result[0]->jenis_pembayaran ?? 'CICILAN';

        $msg = "Pembayaran {$jenis} Rp " . number_format($nominalDibayar, 0, ',', '.')
             . " untuk \"{$nama}\" berhasil!";

        if ($sisaHutang > 0) {
            $msg .= " Sisa hutang: Rp " . number_format($sisaHutang, 0, ',', '.');
        }

        return redirect()->route('kasbon.index')->with('success', $msg);
    }
}