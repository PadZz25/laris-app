@extends('layouts.app')

@section('title', 'Laporan & Omzet')
@section('page-title', 'Laporan & Omzet')

@section('content')
    <div style="display: flex; align-items: center; justify-content: center; flex: 1;">
        <div style="background: #ffffff; border-radius: 20px; padding: 60px 40px; text-align: center; max-width: 560px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);">

            <div style="font-size: 72px; color: #429198; margin-bottom: 20px;">
                <i class="fa-solid fa-chart-line"></i>
            </div>

            <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">
                Laporan & Omzet
            </h1>

            <p style="font-size: 13px; color: #64748b; line-height: 1.7; margin-bottom: 28px;">
                Halaman ini akan menampilkan ringkasan omzet toko, laporan penjualan, uang riil masuk, piutang kasbon, dan peringatan reorder stok.
            </p>

            <div style="display: inline-flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                <span style="background: #fef9c3; color: #a16207; padding: 8px 16px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                    <i class="fa-solid fa-hourglass-half"></i> Sedang Dikembangkan
                </span>
                <span style="background: #e0f2fe; color: #0284c7; padding: 8px 16px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                    <i class="fa-solid fa-rocket"></i> Segera Hadir
                </span>
            </div>

            <div style="margin-top: 40px; padding-top: 24px; border-top: 1px dashed #e2e8f0; font-size: 11px; color: #94a3b8; line-height: 1.6;">
                Sementara ini, kamu bisa mengakses modul yang sudah tersedia di sidebar kiri.<br>
                Modul Operasional: <strong style="color: #0f172a;">Barang & Stok</strong>, <strong style="color: #0f172a;">Buku Kasbon</strong>, <strong style="color: #0f172a;">Pasokan Supplier</strong>, <strong style="color: #0f172a;">Pengeluaran</strong>.
            </div>

        </div>
    </div>
@endsection