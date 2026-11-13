@php
    $toko = \App\Models\Pengaturan::current();
    $namaTokoPdf = $toko->nama_toko ?? 'Toko Ina';
@endphp
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan - {{ $namaTokoPdf }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h2 { text-align: center; margin-bottom: 4px; }
        .store-sub { text-align: center; font-size: 10px; color: #444; margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Laporan Penjualan {{ $namaTokoPdf }}</h2>
    @if($toko && ($toko->alamat || $toko->no_telepon))
        <p class="store-sub">{{ $toko->alamat ?? '' }}{{ ($toko->alamat && $toko->no_telepon) ? ' | Telp: ' : '' }}{{ $toko->no_telepon ?? '' }}</p>
    @endif
    <p style="text-align: center;">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>No. Nota</th>
                <th>Tanggal & Waktu</th>
                <th>Pelanggan</th>
                <th>Total (Rp)</th>
                <th>Metode</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapPenjualan as $trx)
            <tr>
                <td>{{ $trx->nomor_nota }}</td>
                <td>{{ \Carbon\Carbon::parse($trx->tanggal)->format('d/m/Y H:i') }}</td>
                <td>{{ $trx->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                <td>{{ number_format($trx->total_akhir, 0, ',', '.') }}</td>
                <td>{{ strtoupper($trx->metode_bayar) }}</td>
                <td>{{ $trx->status_bayar === 'lunas' ? 'LUNAS' : 'BELUM LUNAS' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>