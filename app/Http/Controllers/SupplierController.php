<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Produk;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index()
    {
        $supplierList = Supplier::orderBy('nama_supplier')->get();

        $faktur = Pembelian::with('supplier')
            ->orderByDesc('tanggal')
            ->orderByDesc('id_pembelian')
            ->get();

        $bulanIni = Pembelian::whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year);

        $stats = [
            'total_pasokan'  => (clone $bulanIni)->sum('total_pembelian'),
            'faktur_lunas'   => Pembelian::where('status_bayar', 'lunas')->count(),
            'utang_tempo'    => Pembelian::where('status_bayar', 'tempo')->sum('total_pembelian'),
            'total_supplier' => Supplier::count(),
        ];

        $produkList   = Produk::orderBy('nama_produk')->get();
        $kategoriList = \App\Models\KategoriProduk::orderBy('nama_kategori')->get();

        return view('supplier.index', compact('supplierList', 'faktur', 'stats', 'produkList', 'kategoriList'));
    }


    // ─── SUPPLIER CRUD ───
    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'nama_supplier'     => ['required', 'string', 'max:255'],
            'nama_sales'        => ['nullable', 'string', 'max:255'],
            'no_telepon'        => ['nullable', 'string', 'max:30'],
            'jadwal_kunjungan'  => ['nullable', 'string', 'max:255'],
            'alamat'            => ['nullable', 'string', 'max:255'],
            'keterangan'        => ['nullable', 'string', 'max:255'],
        ], [
            'nama_supplier.required' => 'Nama supplier wajib diisi.',
        ]);

        try {
            DB::statement('CALL sp_tambah_supplier(?, ?, ?, ?, ?, ?)', [
                $validated['nama_supplier'],
                $validated['no_telepon'] ?? null,
                $validated['alamat'] ?? null,
                $validated['keterangan'] ?? null,
                $validated['nama_sales'] ?? null,
                $validated['jadwal_kunjungan'] ?? null,
            ]);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal simpan supplier: ' . $e->getMessage());
        }

        return redirect()->route('supplier.index')
            ->with('success', "Supplier \"{$validated['nama_supplier']}\" berhasil ditambahkan!");
    }

    public function destroySupplier($id)
    {
        $supplier = Supplier::findOrFail($id);
        $nama = $supplier->nama_supplier;

        try {
            DB::statement('CALL sp_hapus_supplier(?)', [$id]);
        } catch (\Exception $e) {
            return redirect()->route('supplier.index')
                ->with('error', "Tidak bisa hapus \"{$nama}\": masih punya riwayat faktur.");
        }

        return redirect()->route('supplier.index')
            ->with('success', "Supplier \"{$nama}\" berhasil dihapus!");
    }

    // ─── PASAKAN MASUK ───
    public function storePasokan(Request $request)
    {
        $validated = $request->validate([
            'nomor_pembelian'      => ['required', 'string', 'max:255', 'unique:pembelian,nomor_pembelian'],
            'id_supplier'          => ['required', 'exists:supplier,id_supplier'],
            'status_bayar'         => ['required', 'in:lunas,tempo'],
            'tanggal_jatuh_tempo'  => ['nullable', 'date', 'required_if:status_bayar,tempo'],
            'keterangan'           => ['nullable', 'string', 'max:255'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.id_produk'    => ['required', 'exists:produk,id_produk'],
            'items.*.jumlah'       => ['required', 'numeric', 'min:0.01'],
            'items.*.harga_beli'   => ['required', 'numeric', 'min:0'],
        ], [
            'nomor_pembelian.required'  => 'Nomor faktur wajib diisi.',
            'nomor_pembelian.unique'    => 'Nomor faktur sudah dipakai.',
            'id_supplier.required'      => 'Supplier wajib dipilih.',
            'items.required'            => 'Minimal 1 item barang.',
            'items.min'                 => 'Minimal 1 item barang.',
        ]);

        DB::beginTransaction();
        try {
            // Insert header
            $idPembelian = DB::table('pembelian')->insertGetId([
                'nomor_pembelian'     => $validated['nomor_pembelian'],
                'id_supplier'         => $validated['id_supplier'],
                'id_karyawan'         => auth()->user()->id_karyawan,
                'tanggal'             => now(),
                'total_pembelian'     => 0,
                'keterangan'          => $validated['keterangan'] ?? null,
                'status_bayar'        => $validated['status_bayar'],
                'tanggal_jatuh_tempo' => $validated['status_bayar'] === 'tempo'
                                            ? $validated['tanggal_jatuh_tempo']
                                            : null,
            ]);

            // Insert detail — trigger akan otomatis update stok & total
            foreach ($validated['items'] as $item) {
                $subtotal = $item['jumlah'] * $item['harga_beli'];

                DB::table('detail_pembelian')->insert([
                    'id_pembelian'        => $idPembelian,
                    'id_produk'           => $item['id_produk'],
                    'jumlah'              => $item['jumlah'],
                    'harga_beli_saat_itu' => $item['harga_beli'],
                    'subtotal'            => $subtotal,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal simpan pasokan: ' . $e->getMessage());
        }

        $totalItem = count($validated['items']);
        return redirect()->route('supplier.index')
            ->with('success', "Pasokan \"{$validated['nomor_pembelian']}\" ($totalItem item) berhasil dicatat & stok bertambah!");
    }

    public function destroyFaktur($id)
    {
        $faktur = Pembelian::findOrFail($id);
        $nomor = $faktur->nomor_pembelian;

        if ($faktur->status_bayar === 'lunas') {
            return redirect()->route('supplier.index')
                ->with('error', "Faktur \"{$nomor}\" sudah lunas. Tidak bisa dihapus.");
        }

        DB::beginTransaction();
        try {
            // Hapus detail dulu (trigger akan auto-kurangi stok)
            DB::table('detail_pembelian')->where('id_pembelian', $id)->delete();

            // Hapus header
            $faktur->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('supplier.index')
                ->with('error', 'Gagal hapus faktur: ' . $e->getMessage());
        }

        return redirect()->route('supplier.index')
            ->with('success', "Faktur \"{$nomor}\" berhasil dihapus & stok dikembalikan!");
    }

    // ─── DETAIL & PELUNASAN ───
    public function detail($id)
    {
        $faktur = Pembelian::with(['supplier', 'karyawan', 'detail.produk'])
            ->findOrFail($id);

        $items = $faktur->detail->map(fn ($d) => [
            'nama_produk' => $d->produk->nama_produk ?? '-',
            'jumlah'      => (float) $d->jumlah,
            'satuan'      => $d->produk->satuan ?? '',
            'harga_beli'  => (float) $d->harga_beli_saat_itu,
            'subtotal'    => (float) $d->subtotal,
        ]);

        return response()->json([
            'faktur' => [
                'id_pembelian'        => $faktur->id_pembelian,
                'nomor_pembelian'     => $faktur->nomor_pembelian,
                'tanggal'             => $faktur->tanggal->format('d M Y'),
                'nama_supplier'       => $faktur->supplier->nama_supplier ?? '-',
                'nama_sales'          => $faktur->supplier->nama_sales ?? '-',
                'no_telepon'          => $faktur->supplier->no_telepon ?? '-',
                'nama_penerima'       => $faktur->karyawan->nama_karyawan ?? '-',
                'total_pembelian'     => (float) $faktur->total_pembelian,
                'status_bayar'        => $faktur->status_bayar,
                'tanggal_jatuh_tempo' => $faktur->tanggal_jatuh_tempo?->format('d M Y'),
                'keterangan'          => $faktur->keterangan,
            ],
            'items' => $items,
        ]);
    }

    public function bayarFaktur($id)
    {
        $faktur = Pembelian::findOrFail($id);
        $nomor = $faktur->nomor_pembelian;

        try {
            DB::statement('CALL sp_bayar_pembelian(?)', [$id]);
        } catch (\Exception $e) {
            return redirect()->route('supplier.index')
                ->with('error', 'Gagal proses pelunasan: ' . $e->getMessage());
        }

        return redirect()->route('supplier.index')
            ->with('success', "Faktur \"{$nomor}\" berhasil dilunasi!");
    }

    /**
     * Tambah produk baru dari modal pasokan (AJAX).
     */
    public function storeProdukBaru(Request $request)
    {
        $validated = $request->validate([
            'barcode'     => ['required', 'string', 'max:255', 'unique:produk,barcode'],
            'nama_produk' => ['required', 'string', 'max:255'],
            'id_kategori' => ['nullable', 'required_without:new_kategori', 'exists:kategori_produk,id_kategori'],
            'new_kategori'=> ['nullable', 'required_without:id_kategori', 'string', 'max:255', 'unique:kategori_produk,nama_kategori'],
            'satuan'      => ['required', 'string', 'max:50'],
            'harga_beli'  => ['required', 'numeric', 'min:0'],
            'harga_jual'  => ['required', 'numeric', 'min:0', 'gte:harga_beli'],
            'min_stok'    => ['required', 'integer', 'min:0'],
        ], [
            'barcode.required'      => 'Kode barcode wajib diisi.',
            'barcode.unique'        => 'Barcode sudah dipakai produk lain.',
            'nama_produk.required'  => 'Nama produk wajib diisi.',
            'satuan.required'       => 'Satuan wajib diisi.',
            'harga_beli.required'   => 'Harga beli wajib diisi.',
            'harga_jual.required'   => 'Harga jual wajib diisi.',
            'harga_jual.gte'        => 'Harga jual tidak boleh lebih rendah dari harga beli.',
            'new_kategori.unique'   => 'Nama kategori sudah ada.',
        ]);

        // Handle kategori baru
        if (!empty($validated['new_kategori'])) {
            $kat = \App\Models\KategoriProduk::create(['nama_kategori' => $validated['new_kategori']]);
            $validated['id_kategori'] = $kat->id_kategori;
        }
        unset($validated['new_kategori']);

        $produk = Produk::create(array_merge($validated, ['stok_sekarang' => 0]));

        // Load relasi kategori
        $produk->load('kategori');

        return response()->json([
            'success' => true,
            'message' => "Produk \"{$produk->nama_produk}\" berhasil ditambahkan!",
            'produk'  => [
                'id'       => $produk->id_produk,
                'nama'     => $produk->nama_produk,
                'barcode'  => $produk->barcode,
                'kategori' => $produk->kategori->nama_kategori ?? 'Tanpa Kategori',
                'satuan'   => $produk->satuan,
                'harga'    => (float) $produk->harga_beli,
            ],
            'kategori_list' => \App\Models\KategoriProduk::orderBy('nama_kategori')
                ->get(['id_kategori', 'nama_kategori']),
        ]);
    }
}