@extends('layouts.app')

@section('title', 'Barang & Stok')
@section('page-title', 'Barang & Stok')

@section('content')
    <div style="background: #ffffff; border-radius: 16px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">

        <h2 style="font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 14px;">
            🧪 Test Data (Sub-Fase 1.1)
        </h2>

        <p style="font-size: 12px; color: #64748b; margin-bottom: 16px;">
            Kalau tabel di bawah ini muncul dengan data, berarti backend sudah terhubung dengan benar.
        </p>

        {{-- Stats --}}
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px;">
            <div style="background: #e0f2fe; padding: 12px; border-radius: 10px;">
                <div style="font-size: 10px; font-weight: 700; color: #0284c7;">TOTAL JENIS</div>
                <div style="font-size: 20px; font-weight: 800; color: #0f172a;">{{ $stats['total_jenis'] }} Produk</div>
            </div>
            <div style="background: #fef9c3; padding: 12px; border-radius: 10px;">
                <div style="font-size: 10px; font-weight: 700; color: #a16207;">STOK MENIPIS</div>
                <div style="font-size: 20px; font-weight: 800; color: #0f172a;">{{ $stats['stok_menipis'] }} Produk</div>
            </div>
            <div style="background: #fee2e2; padding: 12px; border-radius: 10px;">
                <div style="font-size: 10px; font-weight: 700; color: #b91c1c;">STOK HABIS</div>
                <div style="font-size: 20px; font-weight: 800; color: #0f172a;">{{ $stats['stok_habis'] }} Produk</div>
            </div>
            <div style="background: #dcfce7; padding: 12px; border-radius: 10px;">
                <div style="font-size: 10px; font-weight: 700; color: #16a34a;">TOTAL ASET</div>
                <div style="font-size: 20px; font-weight: 800; color: #0f172a;">Rp {{ number_format($stats['total_aset'], 0, ',', '.') }}</div>
            </div>
        </div>

        {{-- Tabel Produk --}}
        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
            <thead>
                <tr style="background: #f8fafc;">
                    <th style="padding: 10px; text-align: left;">Barcode</th>
                    <th style="padding: 10px; text-align: left;">Nama Produk</th>
                    <th style="padding: 10px; text-align: left;">Kategori</th>
                    <th style="padding: 10px; text-align: left;">Satuan</th>
                    <th style="padding: 10px; text-align: right;">Harga Beli</th>
                    <th style="padding: 10px; text-align: right;">Harga Jual</th>
                    <th style="padding: 10px; text-align: center;">Stok</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($produk as $p)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px; font-family: monospace;">{{ $p->barcode }}</td>
                        <td style="padding: 10px; font-weight: 700;">{{ $p->nama_produk }}</td>
                        <td style="padding: 10px;">{{ $p->kategori->nama_kategori ?? '-' }}</td>
                        <td style="padding: 10px;">{{ $p->satuan }}</td>
                        <td style="padding: 10px; text-align: right;">Rp {{ number_format($p->harga_beli, 0, ',', '.') }}</td>
                        <td style="padding: 10px; text-align: right;">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                        <td style="padding: 10px; text-align: center; font-weight: 800;">{{ $p->stok_sekarang }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 20px; text-align: center; color: #94a3b8;">
                            Tidak ada produk.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
@endsection