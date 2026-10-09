@extends('layouts.app')

@section('title', 'Kasir')
@section('page-title', 'Kasir')

@section('content')
    <div style="display: flex; align-items: center; justify-content: center; flex: 1;">
        <div style="background: #ffffff; border-radius: 20px; padding: 60px 40px; text-align: center; max-width: 560px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);">

            <div style="font-size: 72px; color: #429198; margin-bottom: 20px;">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>

            <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">
                Kasir LARIS
            </h1>

            <p style="font-size: 13px; color: #64748b; line-height: 1.7; margin-bottom: 28px;">
                Halaman kasir sedang dalam pengembangan. Layout grid produk + keranjang akan ditambahkan di Sub-Fase 5.2.
            </p>

            <div style="display: inline-flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                <span style="background: #fef9c3; color: #a16207; padding: 8px 16px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                    <i class="fa-solid fa-hourglass-half"></i> Sedang Dikerjakan
                </span>
                <span style="background: #e0f2fe; color: #0284c7; padding: 8px 16px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                    <i class="fa-solid fa-box"></i> {{ $produk->count() }} Produk Tersedia
                </span>
            </div>

        </div>
    </div>
@endsection