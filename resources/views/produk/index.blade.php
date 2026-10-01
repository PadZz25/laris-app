@extends('layouts.app')

@section('title', 'Barang & Stok')
@section('page-title', 'Barang & Stok')

@section('content')

    {{-- ═══════════════════════════════════════════
         STATS CARDS
         ═══════════════════════════════════════════ --}}
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Jenis Produk</div>
                <div class="stat-value">{{ $stats['total_jenis'] }} Barang</div>
            </div>
            <div class="stat-icon blue"><i class="fa-solid fa-boxes-stacked"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Stok Menipis</div>
                <div class="stat-value">{{ $stats['stok_menipis'] }} Produk</div>
            </div>
            <div class="stat-icon yellow"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Stok Habis</div>
                <div class="stat-value">{{ $stats['stok_habis'] }} Produk</div>
            </div>
            <div class="stat-icon red"><i class="fa-solid fa-circle-xmark"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Nilai Aset Stok</div>
                <div class="stat-value">Rp {{ number_format($stats['total_aset'], 0, ',', '.') }}</div>
            </div>
            <div class="stat-icon green"><i class="fa-solid fa-vault"></i></div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
         CONTROL & FILTER CARD
         ═══════════════════════════════════════════ --}}
    <section class="control-card">
        <div class="control-left">
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input
                    type="text"
                    id="searchInput"
                    class="search-input"
                    placeholder="Cari Nama Barang / Kode Barcode..."
                    oninput="filterProduk()"
                >
            </div>
            <div class="category-group" id="categoryPills">
                <div class="cat-pill active" data-kategori="Semua" onclick="setKategori('Semua', this)">Semua</div>
                @foreach ($kategoriList as $kat)
                    <div class="cat-pill" data-kategori="{{ $kat->nama_kategori }}" onclick="setKategori('{{ $kat->nama_kategori }}', this)">
                        {{ $kat->nama_kategori }}
                    </div>
                @endforeach
            </div>
        </div>

        <button class="btn-primary" onclick="alert('Fitur Tambah Produk akan dibuat di Sub-Fase 1.3')">
            <i class="fa-solid fa-plus"></i> Tambah Barang Baru
        </button>
    </section>

{{-- ═══════════════════════════════════════════
     DATA TABLE PRODUK (full-height, scroll di dalam)
     ═══════════════════════════════════════════ --}}
<section class="table-card">
    <div class="table-card-scroll">
        <table class="data-table" id="produkTable">
            <thead>
                <tr>
                    <th>Kode / SKU</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th style="text-align: right;">Harga Beli</th>
                    <th style="text-align: right;">Harga Jual</th>
                    <th style="text-align: center;">Sisa Stok</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="produkTbody">
                @forelse ($produk as $p)
                    @php
                        if ($p->isStokHabis()) {
                            $badgeClass = 'badge-danger';
                            $badgeIcon = 'fa-xmark';
                            $badgeText = 'Habis';
                            $stokColor = '#dc2626';
                        } elseif ($p->isStokMenipis()) {
                            $badgeClass = 'badge-warning';
                            $badgeIcon = 'fa-triangle-exclamation';
                            $badgeText = 'Restock';
                            $stokColor = '#ca8a04';
                        } else {
                            $badgeClass = 'badge-safe';
                            $badgeIcon = 'fa-check';
                            $badgeText = 'Aman';
                            $stokColor = '#16a34a';
                        }
                    @endphp
                    <tr data-kategori="{{ $p->kategori->nama_kategori ?? 'Tanpa Kategori' }}">
                        <td><span class="code-badge">{{ $p->barcode ?? '-' }}</span></td>
                        <td style="font-weight: 800;">{{ $p->nama_produk }}</td>
                        <td>{{ $p->kategori->nama_kategori ?? '-' }}</td>
                        <td>{{ $p->satuan ?? '-' }}</td>
                        <td style="text-align: right;">Rp {{ number_format($p->harga_beli, 0, ',', '.') }}</td>
                        <td style="text-align: right;">Rp {{ number_format($p->harga_jual, 0, ',', '.') }}</td>
                        <td style="text-align: center; font-weight: 800; color: {{ $stokColor }};">
                            {{ $p->stok_sekarang }} {{ $p->satuan ?? '' }}
                        </td>
                        <td style="text-align: center;">
                            <span class="badge {{ $badgeClass }}">
                                <i class="fa-solid {{ $badgeIcon }}"></i> {{ $badgeText }}
                            </span>
                        </td>
                        <td>
                            <div class="action-btns-cell">
                                <button class="btn-icon" title="Edit Produk"
                                        onclick="alert('Fitur Edit akan dibuat di Sub-Fase 1.4')">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn-icon danger" title="Hapus Produk"
                                        onclick="alert('Fitur Hapus akan dibuat di Sub-Fase 1.4')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fa-solid fa-box-open"></i>
                                Belum ada produk. Klik "Tambah Barang Baru" untuk mulai.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer info di dalam card --}}
    <div class="table-card-footer">
        Menampilkan <strong id="visibleCount">{{ $produk->count() }}</strong>
        dari <strong>{{ $produk->count() }}</strong> produk
    </div>
</section>

@endsection

@push('scripts')
<script>
    let activeKategori = 'Semua';

    function setKategori(kategori, el) {
        activeKategori = kategori;
        // Update visual pill
        document.querySelectorAll('#categoryPills .cat-pill').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        filterProduk();
    }

    function filterProduk() {
        const keyword = document.getElementById('searchInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('#produkTbody tr[data-kategori]');
        let visible = 0;

        rows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const rowKat = row.dataset.kategori;

            const matchSearch = keyword === '' || rowText.includes(keyword);
            const matchKat = activeKategori === 'Semua' || rowKat === activeKategori;

            if (matchSearch && matchKat) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('visibleCount').textContent = visible;
    }
</script>
@endpush