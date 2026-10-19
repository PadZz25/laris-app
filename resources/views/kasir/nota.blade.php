<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $penjualan->nomor_nota }} - Toko Ina</title>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #cbd5e1;
            font-family: 'Courier Prime', monospace, sans-serif;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            min-height: 100vh; padding: 20px;
        }
        .receipt-container {
            width: 58mm; min-width: 260px; max-width: 280px;
            background-color: #ffffff; padding: 14px 10px;
            border-radius: 4px; box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            color: #000000; font-size: 11px; line-height: 1.3;
        }
        .dashed-line { border-bottom: 1px dashed #000000; margin: 8px 0; }
        .receipt-header { text-align: center; }
        .store-name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 18px; font-weight: 800;
            letter-spacing: -0.5px; text-transform: uppercase;
        }
        .store-info { font-size: 9.5px; margin-top: 2px; }
        .meta-row { display: flex; justify-content: space-between; font-size: 10px; }
        .item-block { margin-bottom: 6px; }
        .item-name { font-weight: 700; font-size: 10.5px; word-break: break-word; }
        .item-calc { display: flex; justify-content: space-between; font-size: 10px; padding-left: 6px; }
        .total-row { display: flex; justify-content: space-between; font-size: 10.5px; margin-bottom: 3px; }
        .total-row.grand-total { font-weight: 700; font-size: 12px; margin-top: 4px; }
        .receipt-footer { text-align: center; font-size: 9.5px; margin-top: 6px; }
        .print-btn-container { margin-top: 20px; display: flex; gap: 10px; }
        .btn-print {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a; color: #ffffff;
            border: none; padding: 10px 20px; border-radius: 8px;
            font-weight: 700; font-size: 12px; cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .badge-kasbon {
            text-align: center; font-weight: 700; font-size: 11px;
            padding: 4px 0; margin: 4px 0; border: 1px solid #000000;
        }
        @media print {
            body { background-color: #ffffff; padding: 0; margin: 0; }
            .receipt-container { width: 58mm; max-width: 58mm; box-shadow: none; padding: 0; margin: 0; border-radius: 0; }
            .print-btn-container { display: none; }
            @page { size: 58mm auto; margin: 0mm; }
        }
    </style>
</head>
<body>

    <div class="receipt-container" id="receipt">
        <div class="receipt-header">
            <div class="store-name">TOKO INA</div>
            <div class="store-info">Desa Wates, Kec. Wates, Blitar</div>
            <div class="store-info">Telp/WA: 0812-3456-7890</div>
        </div>

        <div class="dashed-line"></div>

        <div class="meta-row">
            <span>No. Nota : {{ $penjualan->nomor_nota }}</span>
        </div>
        <div class="meta-row">
            <span>Tgl      : {{ \Carbon\Carbon::parse($penjualan->tanggal)->format('d/m/Y H:i') }}</span>
        </div>
        <div class="meta-row">
            <span>Kasir    : {{ $penjualan->karyawan->nama_karyawan ?? '-' }}</span>
        </div>
        
        @if($penjualan->metode_bayar === 'hutang')
            <div class="meta-row" style="font-weight:700; margin-top: 2px;">
                <span>Warga    : {{ $penjualan->pelanggan->nama_pelanggan ?? '-' }}</span>
            </div>
        @endif

        <div class="dashed-line"></div>

        @foreach($penjualan->detail as $item)
            <div class="item-block">
                <div class="item-name">{{ $item->produk->nama_produk ?? '-' }}</div>
                <div class="item-calc">
                    <span>{{ (int)$item->jumlah }} x {{ number_format($item->harga_jual_saat_itu, 0, ',', '.') }}</span>
                    <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach

        <div class="dashed-line"></div>

        <div class="total-row">
            <span>Subtotal</span>
            <span>{{ number_format($penjualan->total_belanja, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span>Diskon</span>
            <span>{{ number_format($penjualan->diskon, 0, ',', '.') }}</span>
        </div>
        <div class="total-row grand-total">
            <span>TOTAL</span>
            <span>Rp {{ number_format($penjualan->total_akhir, 0, ',', '.') }}</span>
        </div>

        <div class="dashed-line"></div>

        @if($penjualan->metode_bayar === 'hutang')
            <div class="badge-kasbon">*** NOTA KASBON WARGA ***</div>
            <div class="total-row">
                <span>Metode Bayar</span>
                <span>KASBON</span>
            </div>
            <div class="total-row">
                <span>Status</span>
                <span>BELUM LUNAS</span>
            </div>
            <div class="total-row">
                <span>Bayar Tunai</span>
                <span>0</span>
            </div>
            <div class="total-row" style="font-weight: 700;">
                <span>Sisa Kasbon</span>
                <span>{{ number_format($penjualan->total_akhir, 0, ',', '.') }}</span>
            </div>
        @else
            <div class="total-row">
                <span>Metode Bayar</span>
                <span>{{ strtoupper($penjualan->metode_bayar) }}</span>
            </div>
            <div class="total-row">
                <span>Bayar</span>
                <span>{{ number_format($penjualan->uang_diterima, 0, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span>Kembali</span>
                <span>{{ number_format($penjualan->kembalian, 0, ',', '.') }}</span>
            </div>
        @endif

        <div class="dashed-line"></div>

        <div class="receipt-footer">
            <div>Terima kasih sudah berbelanja</div>
            <div>di Toko Ina</div>
        </div>
    </div>

    <div class="print-btn-container">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk (Ctrl + P)</button>
        <button class="btn-print" onclick="window.close()" style="background:#64748b;">Tutup</button>
    </div>

    <script>
        // Otomatis buka dialog print saat halaman dimuat
        window.onload = function() {
            setTimeout(function() { window.print(); }, 500);
        };
    </script>
</body>
</html>