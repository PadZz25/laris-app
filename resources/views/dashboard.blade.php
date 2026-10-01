@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="page-scroll">
        <div style="background: #ffffff; border-radius: 16px; padding: 40px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
            <i class="fa-solid fa-rocket"></i>
        </div>
        <h2 style="font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">
            Selamat Datang di LARIS!
        </h2>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 24px;">
            Login berhasil sebagai <strong>{{ auth()->user()->nama_karyawan }}</strong> ({{ ucfirst(auth()->user()->peran) }}).
        </p>

        <div style="display: inline-flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
            <span style="background: #e0f2fe; color: #0284c7; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                <i class="fa-solid fa-check-circle"></i> Auth Backend OK
            </span>
            <span style="background: #dcfce7; color: #16a34a; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                <i class="fa-solid fa-check-circle"></i> Layout Blade OK
            </span>
            <span style="background: #fef9c3; color: #a16207; padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                <i class="fa-solid fa-hourglass-half"></i> Modul Menyusul
            </span>
        </div>

        <div style="margin-top: 32px; font-size: 12px; color: #94a3b8;">
            Modul berikutnya: <strong>Produk & Stok</strong>, <strong>Kasir/POS</strong>, <strong>Kasbon</strong>...
        </div>
    </div>
@endsection