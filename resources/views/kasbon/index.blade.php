@extends('layouts.app')

@section('title', 'Buku Kasbon')
@section('page-title', 'Buku Kasbon')

@section('content')

    {{-- STATS CARDS --}}
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Warga Terdaftar</div>
                <div class="stat-value">{{ $stats['total_warga'] }} Warga</div>
            </div>
            <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Warga Ada Kasbon</div>
                <div class="stat-value" style="color: #dc2626;">{{ $stats['warga_kasbon'] }} Warga</div>
            </div>
            <div class="stat-icon red"><i class="fa-solid fa-book"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Warga Bebas Utang</div>
                <div class="stat-value" style="color: #16a34a;">{{ $stats['warga_bebas'] }} Warga</div>
            </div>
            <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Piutang Berjalan</div>
                <div class="stat-value" style="color: #dc2626;">
                    Rp {{ number_format($stats['total_piutang'], 0, ',', '.') }}
                </div>
            </div>
            <div class="stat-icon yellow"><i class="fa-solid fa-money-bill-wave"></i></div>
        </div>
    </section>

    {{-- CONTROL --}}
    <section class="control-card">
        <div class="control-left">
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" class="search-input"
                       placeholder="Cari Nama Warga / Pelanggan..."
                       oninput="filterKasbon()">
            </div>
            <div class="category-group">
                <div class="cat-pill active" data-status="Semua" onclick="setFilterStatus('Semua', this)">Semua Warga</div>
                <div class="cat-pill" data-status="Ada Kasbon" onclick="setFilterStatus('Ada Kasbon', this)">Ada Kasbon</div>
                <div class="cat-pill" data-status="Bebas Utang" onclick="setFilterStatus('Bebas Utang', this)">Bebas Utang</div>
            </div>
        </div>

        <button type="button" class="btn-primary" onclick="openModalTambahWarga()">
            <i class="fa-solid fa-plus"></i> Tambah Warga
        </button>
    </section>

    {{-- LAYOUT SPLIT --}}
    <div class="kasbon-layout">

        <div class="kasbon-main">
            <div class="warga-grid" id="wargaGrid">
                @forelse ($pelanggan as $p)
                    @php
                        $colors = ['#0ea5e9','#8b5cf6','#ec4899','#f59e0b','#10b981','#ef4444','#06b6d4','#a855f7'];
                        $avatarColor = $colors[crc32($p->nama_pelanggan) % count($colors)];
                        $initial = strtoupper(mb_substr($p->nama_pelanggan, 0, 1));
                    @endphp
                    <div class="warga-card"
                         data-id="{{ $p->id_pelanggan }}"
                         data-status="{{ $p->status_piutang }}"
                         data-search="{{ strtolower($p->nama_pelanggan . ' ' . ($p->no_telepon ?? '') . ' ' . ($p->alamat ?? '')) }}"
                         onclick="selectWarga({{ $p->id_pelanggan }}, this)">
                        <div class="warga-avatar" style="background: {{ $avatarColor }};">
                            {{ $initial }}
                        </div>
                        <div class="warga-info">
                            <div class="warga-nama">{{ $p->nama_pelanggan }}</div>
                            <div class="warga-alamat">{{ $p->alamat ?? 'Alamat tidak diisi' }}</div>
                        </div>
                        <div class="warga-nominal">
                            <div class="warga-nominal-value {{ $p->total_hutang > 0 ? 'hutang' : 'lunas' }}">
                                Rp {{ number_format($p->total_hutang, 0, ',', '.') }}
                            </div>
                            @if ($p->total_hutang > 0)
                                <div class="warga-badge hutang">Ada Kasbon</div>
                            @else
                                <div class="warga-badge lunas">Lunas</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="grid-column: span 2;">
                        <div class="empty-state">
                            <i class="fa-solid fa-users"></i>
                            Belum ada warga terdaftar.<br>Klik "Tambah Warga" untuk mulai.
                        </div>
                    </div>
                @endforelse
            </div>

            <div style="text-align: right; font-size: 11px; color: #64748b; padding: 0 4px;">
                Menampilkan <strong id="visibleCount" style="color: #0f172a;">{{ $pelanggan->count() }}</strong>
                dari <strong style="color: #0f172a;">{{ $pelanggan->count() }}</strong> warga
            </div>
        </div>

        <aside class="kasbon-side">
            <div class="kasbon-side-header">
                <div class="kasbon-side-title">Detail & Pelunasan</div>
            </div>

            <div class="kasbon-side-body" id="sidePanelBody">
                <div class="kasbon-empty-side" id="sideEmptyState">
                    <i class="fa-solid fa-user"></i>
                    <div class="title">Belum Ada Warga Dipilih</div>
                    <div class="sub">Silakan pilih salah satu kartu warga di sebelah kiri untuk melihat rincian nota kasbon dan melakukan pelunasan.</div>
                </div>

                <div id="sideDetailPanel" style="display: none;">
                    <div class="kasbon-info-card">
                        <div class="kasbon-info-nama" id="sideNama">—</div>
                        <div class="kasbon-info-meta" id="sideMeta">—</div>
                        <div class="kasbon-info-saldo">
                            Saldo Hutang: <strong id="sideSaldo">Rp 0</strong>
                        </div>
                    </div>

                    <div style="font-size: 11px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">
                        Riwayat Nota Kasbon (Belum Lunas)
                    </div>
                    <div class="kasbon-nota-list" id="sideNotaList"></div>
                </div>
            </div>

            <div class="kasbon-side-footer">
                <button type="button" class="btn-bayar-full" id="btnBayarKasbon"
                        onclick="openModalPelunasan()" disabled>
                    <i class="fa-solid fa-money-bill-wave"></i> Bayar Kasbon
                </button>
            </div>
        </aside>

    </div>

    {{-- MODAL: TAMBAH WARGA --}}
    <div class="modal-overlay" id="modalTambahWarga">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fa-solid fa-user-plus"></i> Tambah Warga / Pelanggan Baru</h3>
                <button type="button" class="btn-close-modal" onclick="closeModalTambahWarga()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('kasbon.store') }}"
                  style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
                @csrf

                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Lengkap Warga <span class="required">*</span></label>
                        <input type="text" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}"
                               placeholder="Misal: Pak Joko, Bu Ani..." required>
                        @error('nama_pelanggan')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Alamat / RT / Dusun</label>
                        <input type="text" name="alamat" value="{{ old('alamat') }}"
                               placeholder="Misal: Jln. Mawar RT 02 / Desa Wates">
                    </div>

                    <div class="form-group">
                        <label>Nomor Telepon / WhatsApp (Opsional)</label>
                        <input type="text" name="no_telepon" value="{{ old('no_telepon') }}"
                               placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="form-group">
                        <label>Nominal Kasbon Awal (Rp)</label>
                        <input type="number" name="kasbon_awal" value="{{ old('kasbon_awal') }}"
                               placeholder="0 (isi jika ada utang awal)" min="0" step="any">
                        <div style="font-size: 10px; color: #94a3b8; margin-top: 4px;">
                            Isi jika warga ini sudah punya utang sebelumnya. Biarkan 0 jika belum ada.
                        </div>
                        @error('kasbon_awal')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalTambahWarga()">Batal</button>
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Warga
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: PELUNASAN --}}
    <div class="modal-overlay" id="modalPelunasan">
        <div class="modal-card modal-card-sm">
            <div class="modal-header">
                <h3>Pelunasan Kasbon — <span id="pelunasanNama">-</span></h3>
                <button type="button" class="btn-close-modal" onclick="closeModalPelunasan()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="" id="formPelunasan"
                  style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
                @csrf

                <div class="modal-body">
                    <div class="pelunasan-box">
                        <div class="pelunasan-label">Total Kasbon Tertunggak</div>
                        <div class="pelunasan-value" id="pelunasanTotal">Rp 0</div>
                    </div>

                    <div class="form-group">
                        <label>Nominal Bayar / Cicilan (Rp) <span class="required">*</span></label>
                        <div class="input-with-btn">
                            <input type="number" name="nominal_bayar" id="inputNominalBayar"
                                   min="1" step="any" placeholder="Masukkan nominal..." required>
                            <button type="button" class="btn-lunas-penuh" onclick="isiLunasPenuh()">
                                <i class="fa-solid fa-check"></i> Lunas Penuh
                            </button>
                        </div>
                        <div style="font-size: 10px; color: #94a3b8; margin-top: 4px;">
                            Bisa bayar sebagian (cicilan) atau penuh. Sisa hutang akan dihitung otomatis.
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalPelunasan()">Batal</button>
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-check"></i> Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    let activeStatus = 'Semua';
    let currentWarga = null;

    function setFilterStatus(status, el) {
        activeStatus = status;
        document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        filterKasbon();
    }

    function filterKasbon() {
        const keyword = document.getElementById('searchInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('#wargaGrid .warga-card');
        let visible = 0;

        cards.forEach(card => {
            const matchSearch = keyword === '' || (card.dataset.search || '').includes(keyword);
            const matchStatus = activeStatus === 'Semua' || card.dataset.status === activeStatus;

            if (matchSearch && matchStatus) {
                card.style.display = '';
                visible++;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('visibleCount').textContent = visible;
    }

    async function selectWarga(id, cardEl) {
        document.querySelectorAll('.warga-card').forEach(c => c.classList.remove('active'));
        cardEl.classList.add('active');

        document.getElementById('sideEmptyState').style.display = 'none';
        document.getElementById('sideDetailPanel').style.display = 'block';
        document.getElementById('sideNama').textContent = 'Memuat...';
        document.getElementById('sideMeta').textContent = '';
        document.getElementById('sideSaldo').textContent = 'Rp 0';
        document.getElementById('sideNotaList').innerHTML =
            '<div style="text-align: center; color: #94a3b8; padding: 20px;"><i class="fa-solid fa-spinner fa-spin"></i></div>';
        document.getElementById('btnBayarKasbon').disabled = true;

        try {
            const res = await fetch(`/kasbon/${id}/detail`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            const p = data.pelanggan;
            currentWarga = p;

            document.getElementById('sideNama').textContent = p.nama_pelanggan;
            document.getElementById('sideMeta').textContent = [
                p.alamat || null,
                p.no_telepon || null,
            ].filter(Boolean).join(' • ') || 'Data kontak tidak diisi';
            document.getElementById('sideSaldo').textContent =
                'Rp ' + p.total_hutang.toLocaleString('id-ID');

            renderNotaList(data.nota);
            document.getElementById('btnBayarKasbon').disabled = p.total_hutang <= 0;
        } catch (err) {
            console.error(err);
            document.getElementById('sideNotaList').innerHTML =
                '<div style="text-align: center; color: #dc2626; padding: 20px; font-size: 11px;">Gagal memuat data.</div>';
        }
    }

    function renderNotaList(notaList) {
        const box = document.getElementById('sideNotaList');
        if (notaList.length === 0) {
            box.innerHTML = `
                <div style="text-align: center; color: #94a3b8; padding: 24px 12px; font-size: 11px;">
                    <i class="fa-solid fa-check-circle" style="font-size: 28px; display: block; margin-bottom: 6px; color: #16a34a;"></i>
                    Tidak ada nota tertunggak
                </div>`;
            return;
        }
        box.innerHTML = notaList.map(n => `
            <div class="kasbon-nota-item">
                <div style="min-width: 0; flex: 1;">
                    <div class="kasbon-nota-nomor">#${n.nomor_nota}</div>
                    <div class="kasbon-nota-tanggal">${n.tanggal}</div>
                </div>
                <div class="kasbon-nota-nominal">Rp ${n.total_akhir.toLocaleString('id-ID')}</div>
            </div>
        `).join('');
    }

    function openModalTambahWarga() {
        document.getElementById('modalTambahWarga').classList.add('active');
    }
    function closeModalTambahWarga() {
        document.getElementById('modalTambahWarga').classList.remove('active');
    }

    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            openModalTambahWarga();
        });
    @endif

    function openModalPelunasan() {
        if (!currentWarga || currentWarga.total_hutang <= 0) return;
        document.getElementById('pelunasanNama').textContent = currentWarga.nama_pelanggan;
        document.getElementById('pelunasanTotal').textContent =
            'Rp ' + currentWarga.total_hutang.toLocaleString('id-ID');
        document.getElementById('inputNominalBayar').value = '';
        document.getElementById('formPelunasan').action = `/kasbon/${currentWarga.id_pelanggan}/bayar`;
        document.getElementById('modalPelunasan').classList.add('active');
        setTimeout(() => document.getElementById('inputNominalBayar').focus(), 100);
    }

    function closeModalPelunasan() {
        document.getElementById('modalPelunasan').classList.remove('active');
    }

    function isiLunasPenuh() {
        if (!currentWarga) return;
        document.getElementById('inputNominalBayar').value = currentWarga.total_hutang;
    }

    document.getElementById('modalTambahWarga').addEventListener('click', function (e) {
        if (e.target === this) closeModalTambahWarga();
    });
    document.getElementById('modalPelunasan').addEventListener('click', function (e) {
        if (e.target === this) closeModalPelunasan();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModalTambahWarga();
            closeModalPelunasan();
        }
    });
</script>
@endpush