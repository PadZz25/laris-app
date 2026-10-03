@extends('layouts.app')

@section('title', 'Pengeluaran')
@section('page-title', 'Pengeluaran')

@section('content')

    {{-- ═══════════════════════════════════════════
         STATS CARDS
         ═══════════════════════════════════════════ --}}
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Pengeluaran Bulan Ini</div>
                <div class="stat-value" style="color: #dc2626;">
                    Rp {{ number_format($stats['total_bulan_ini'], 0, ',', '.') }}
                </div>
            </div>
            <div class="stat-icon red"><i class="fa-solid fa-money-bill-trend-up"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Pengeluaran Hari Ini</div>
                <div class="stat-value">
                    Rp {{ number_format($stats['total_hari_ini'], 0, ',', '.') }}
                </div>
            </div>
            <div class="stat-icon blue"><i class="fa-solid fa-calendar-day"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Kategori Terbesar</div>
                <div class="stat-value" style="color: #1d4ed8;">
                    {{ $stats['kategori_terbesar'] }}
                </div>
            </div>
            <div class="stat-icon yellow"><i class="fa-solid fa-chart-pie"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Transaksi</div>
                <div class="stat-value" style="color: #0284c7;">
                    {{ $stats['total_transaksi'] }} Transaksi
                </div>
            </div>
            <div class="stat-icon green"><i class="fa-solid fa-list-check"></i></div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════
         CONTROL & FILTER
         ═══════════════════════════════════════════ --}}
    <section class="control-card">
        <div class="control-left">
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text"
                       id="searchInput"
                       class="search-input"
                       placeholder="Cari keterangan pengeluaran..."
                       oninput="filterPengeluaran()">
            </div>
            <select id="filterKategori"
                    class="search-input"
                    style="border-radius: 10px; padding: 9px 14px; width: 180px;"
                    onchange="filterPengeluaran()">
                <option value="">Semua Kategori</option>
                @foreach ($kategoriList as $kat)
                    <option value="{{ $kat }}">{{ $kat }}</option>
                @endforeach
            </select>
        </div>

        <button type="button" class="btn-primary" onclick="openModalTambahPengeluaran()">
            <i class="fa-solid fa-plus"></i> Catat Pengeluaran Baru
        </button>
    </section>

    {{-- ═══════════════════════════════════════════
         LAYOUT SPLIT: TABEL + SIDE PANEL
         ═══════════════════════════════════════════ --}}
    <div class="pengeluaran-layout">

        {{-- KIRI: Tabel --}}
        <div class="pengeluaran-main">
            <section class="table-card">
                <div class="table-card-scroll">
                    <table class="data-table" id="pengeluaranTable">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th style="text-align: right;">Nominal</th>
                                <th>Sumber Dana</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="pengeluaranTbody">
                            @forelse ($pengeluaran as $p)
                                @php
                                    $catClass = match($p->kategori) {
                                        'Operasional'    => 'cat-operasional',
                                        'Gaji & Bonus'   => 'cat-gaji',
                                        'Perlengkapan'   => 'cat-perlengkapan',
                                        default          => 'cat-lain',
                                    };
                                @endphp
                                <tr data-kategori="{{ $p->kategori }}"
                                    data-keterangan="{{ strtolower($p->nama_pengeluaran) }}">
                                    <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge-cat {{ $catClass }}">{{ $p->kategori }}</span>
                                    </td>
                                    <td style="font-weight: 700;">{{ $p->nama_pengeluaran }}</td>
                                    <td style="text-align: right; font-weight: 800; color: #dc2626;">
                                        Rp {{ number_format($p->jumlah, 0, ',', '.') }}
                                    </td>
                                    <td style="color: #64748b;">{{ $p->sumber_dana }}</td>
                                    <td>
                                        <div class="action-btns-cell">
                                            <button class="btn-icon danger"
                                                    title="Hapus"
                                                    data-id="{{ $p->id_pengeluaran }}"
                                                    data-nama="{{ $p->nama_pengeluaran }}"
                                                    onclick="openModalHapus(this)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="fa-solid fa-receipt"></i>
                                            Belum ada catatan pengeluaran.<br>
                                            Klik "Catat Pengeluaran Baru" untuk mulai.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-card-footer">
                    Menampilkan <strong id="visibleCount">{{ $pengeluaran->count() }}</strong>
                    dari <strong>{{ $pengeluaran->count() }}</strong> transaksi
                </div>
            </section>
        </div>

        {{-- KANAN: Breakdown Kategori --}}
        <aside class="pengeluaran-side">

            {{-- Summary card --}}
            <div class="breakdown-summary">
                <div class="label">Total Bulan Ini</div>
                <div class="value">Rp {{ number_format($stats['total_bulan_ini'], 0, ',', '.') }}</div>
                <div class="sub">
                    <i class="fa-solid fa-calendar"></i>
                    {{ now()->translatedFormat('F Y') }}
                </div>
            </div>

            {{-- Breakdown kategori --}}
            <div class="breakdown-card">
                <div class="breakdown-title">
                    <i class="fa-solid fa-chart-simple" style="color: #429198;"></i>
                    Breakdown Kategori
                </div>

                @if ($breakdown->isEmpty())
                    <div class="breakdown-empty">
                        <i class="fa-solid fa-chart-pie"></i>
                        Belum ada pengeluaran<br>bulan ini
                    </div>
                @else
                    @php
                        $warnaMap = [
                            'Operasional'    => '#1d4ed8',
                            'Gaji & Bonus'   => '#b45309',
                            'Perlengkapan'   => '#4338ca',
                            'Lain-lain'      => '#7e22ce',
                        ];
                    @endphp
                    @foreach ($breakdown as $b)
                        @php
                            $pct = $totalBulanIni > 0 ? round(($b->total / $totalBulanIni) * 100) : 0;
                            $color = $warnaMap[$b->kategori] ?? '#64748b';
                        @endphp
                        <div class="cat-progress-item">
                            <div class="cat-progress-label">
                                <span>{{ $b->kategori }}</span>
                                <span>Rp {{ number_format($b->total, 0, ',', '.') }} ({{ $pct }}%)</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill"
                                     style="width: {{ $pct }}%; background-color: {{ $color }};"></div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

        </aside>

    </div>

    {{-- ═══════════════════════════════════════════
         MODAL: CATAT PENGELUARAN BARU
         ═══════════════════════════════════════════ --}}
    <div class="modal-overlay" id="modalTambahPengeluaran">
        <div class="modal-card">

            <div class="modal-header">
                <h3><i class="fa-solid fa-wallet"></i> Catat Pengeluaran Baru</h3>
                <button type="button" class="btn-close-modal" onclick="closeModalTambahPengeluaran()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('pengeluaran.store') }}"
                  style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
                @csrf

                <div class="modal-body">

                    <div class="form-grid-2col">
                        <div class="form-group">
                            <label>Tanggal Pengeluaran <span class="required">*</span></label>
                            <input type="date"
                                   name="tanggal"
                                   value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                                   class="@error('tanggal') error @enderror"
                                   required>
                            @error('tanggal')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Sumber Dana <span class="required">*</span></label>
                            <select name="sumber_dana" class="@error('sumber_dana') error @enderror" required>
                                <option value="Kas Kecil Toko" {{ old('sumber_dana') == 'Kas Kecil Toko' ? 'selected' : '' }}>
                                    Kas Kecil Toko / Laci Kasir
                                </option>
                                <option value="Transfer Bank" {{ old('sumber_dana') == 'Transfer Bank' ? 'selected' : '' }}>
                                    Transfer Bank
                                </option>
                            </select>
                            @error('sumber_dana')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Kategori Pengeluaran <span class="required">*</span></label>
                        <select name="kategori" class="@error('kategori') error @enderror" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ old('kategori') == $kat ? 'selected' : '' }}>
                                    {{ $kat }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Keterangan / Rincian Pengeluaran <span class="required">*</span></label>
                        <input type="text"
                               name="nama_pengeluaran"
                               value="{{ old('nama_pengeluaran') }}"
                               placeholder="Misal: Beli kantong plastik sedang 5 pack"
                               class="@error('nama_pengeluaran') error @enderror"
                               required>
                        @error('nama_pengeluaran')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Nominal Pengeluaran (Rp) <span class="required">*</span></label>
                        <input type="number"
                                name="jumlah"
                                value="{{ old('jumlah') }}"
                                placeholder="0"
                                min="0"
                                step="any"
                                class="@error('jumlah') error @enderror"
                                required>
                        @error('jumlah')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalTambahPengeluaran()">
                        Batal
                    </button>
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Pengeluaran
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         MODAL: KONFIRMASI HAPUS
         ═══════════════════════════════════════════ --}}
    <div class="modal-overlay" id="modalHapusPengeluaran">
        <div class="modal-card modal-card-sm">

            <div class="modal-header">
                <h3 style="color: #dc2626;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Konfirmasi Hapus
                </h3>
                <button type="button" class="btn-close-modal" onclick="closeModalHapus()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="modal-info-box warning">
                    <p style="font-weight: 700; font-size: 12px; margin-bottom: 6px;">
                        Anda yakin ingin menghapus catatan pengeluaran ini?
                    </p>
                    <p style="font-size: 11px;">
                        Pengeluaran: <strong id="deleteNamaPengeluaran">-</strong>
                    </p>
                    <p style="font-size: 10px; margin-top: 8px; opacity: 0.8;">
                        Tindakan ini tidak bisa dibatalkan.
                    </p>
                </div>
            </div>

            <form method="POST" action="" id="formHapusPengeluaran" style="display: contents;">
                @csrf
                @method('DELETE')

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalHapus()">Batal</button>
                    <button type="submit" class="btn-danger">
                        <i class="fa-solid fa-trash"></i> Ya, Hapus
                    </button>
                </div>
            </form>

        </div>
    </div>

@endsection

@push('scripts')
<script>
    // ═══════════════════════════════════════════
    // FILTER TABEL
    // ═══════════════════════════════════════════
    function filterPengeluaran() {
        const keyword   = document.getElementById('searchInput').value.toLowerCase().trim();
        const kategori  = document.getElementById('filterKategori').value;
        const rows      = document.querySelectorAll('#pengeluaranTbody tr[data-kategori]');
        let visible     = 0;

        rows.forEach(row => {
            const keterangan = row.dataset.keterangan || '';
            const rowKat     = row.dataset.kategori;

            const matchSearch = keyword === '' || keterangan.includes(keyword);
            const matchKat    = kategori === '' || rowKat === kategori;

            if (matchSearch && matchKat) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('visibleCount').textContent = visible;
    }

    // ═══════════════════════════════════════════
    // MODAL TAMBAH PENGELUARAN
    // ═══════════════════════════════════════════
    function openModalTambahPengeluaran() {
        document.getElementById('modalTambahPengeluaran').classList.add('active');
    }

    function closeModalTambahPengeluaran() {
        document.getElementById('modalTambahPengeluaran').classList.remove('active');
    }

    // Auto-open kalau ada error validasi dari server
    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            openModalTambahPengeluaran();
        });
    @endif

    // ═══════════════════════════════════════════
    // MODAL HAPUS
    // ═══════════════════════════════════════════
    function openModalHapus(btn) {
        const id   = btn.dataset.id;
        const nama = btn.dataset.nama;

        document.getElementById('deleteNamaPengeluaran').textContent = nama;
        document.getElementById('formHapusPengeluaran').action = `/pengeluaran/${id}`;

        document.getElementById('modalHapusPengeluaran').classList.add('active');
    }

    function closeModalHapus() {
        document.getElementById('modalHapusPengeluaran').classList.remove('active');
    }

    // ═══════════════════════════════════════════
    // EVENT LISTENERS
    // ═══════════════════════════════════════════
    document.getElementById('modalTambahPengeluaran').addEventListener('click', function (e) {
        if (e.target === this) closeModalTambahPengeluaran();
    });

    document.getElementById('modalHapusPengeluaran').addEventListener('click', function (e) {
        if (e.target === this) closeModalHapus();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModalTambahPengeluaran();
            closeModalHapus();
        }
    });
</script>
@endpush