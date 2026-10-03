@extends('layouts.app')

@section('title', 'Pasokan Supplier')
@section('page-title', 'Pasokan Supplier')

@section('content')

    {{-- STATS --}}
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Pasokan Bulan Ini</div>
                <div class="stat-value">Rp {{ number_format($stats['total_pasokan'], 0, ',', '.') }}</div>
            </div>
            <div class="stat-icon blue"><i class="fa-solid fa-truck-fast"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Faktur Lunas</div>
                <div class="stat-value" style="color: #16a34a;">{{ $stats['faktur_lunas'] }} Faktur</div>
            </div>
            <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Utang Tempo Toko</div>
                <div class="stat-value" style="color: #dc2626;">Rp {{ number_format($stats['utang_tempo'], 0, ',', '.') }}</div>
            </div>
            <div class="stat-icon red"><i class="fa-solid fa-clock"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Supplier Terdaftar</div>
                <div class="stat-value" style="color: #0284c7;">{{ $stats['total_supplier'] }} Partner</div>
            </div>
            <div class="stat-icon yellow"><i class="fa-solid fa-handshake"></i></div>
        </div>
    </section>

    {{-- CONTROL --}}
    <section class="control-card">
        <div class="control-left">
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" class="search-input"
                       placeholder="Cari No. Faktur / Nama Supplier..."
                       oninput="filterFaktur()">
            </div>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <button type="button" class="btn-secondary" onclick="openModalSupplier()">
                <i class="fa-solid fa-truck"></i> + Supplier Baru
            </button>
            <button type="button" class="btn-primary" onclick="openModalPasokan()">
                <i class="fa-solid fa-plus"></i> Catat Pasokan Baru
            </button>
        </div>
    </section>

    {{-- LAYOUT SPLIT --}}
    <div class="supplier-layout">

        {{-- KIRI: Tabel Faktur --}}
        <div class="supplier-main">
            <section class="table-card">
                <div class="table-card-scroll">
                    <table class="data-table" id="fakturTable">
                        <thead>
                            <tr>
                                <th>No. Faktur</th>
                                <th>Tanggal</th>
                                <th>Supplier</th>
                                <th style="text-align: center;">Item</th>
                                <th style="text-align: right;">Total</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="fakturTbody">
                            @forelse ($faktur as $f)
                                <tr data-search="{{ strtolower($f->nomor_pembelian . ' ' . ($f->supplier->nama_supplier ?? '')) }}"
                                    style="cursor: pointer;"
                                    onclick="selectFaktur({{ $f->id_pembelian }}, this)">
                                    <td style="font-weight: 800;">{{ $f->nomor_pembelian }}</td>
                                    <td>{{ $f->tanggal->format('d M Y') }}</td>
                                    <td>{{ $f->supplier->nama_supplier ?? '-' }}</td>
                                    <td style="text-align: center;">{{ $f->detail->count() ?? 0 }} jenis</td>
                                    <td style="text-align: right; font-weight: 800;">
                                        Rp {{ number_format($f->total_pembelian, 0, ',', '.') }}
                                    </td>
                                    <td style="text-align: center;">
                                        @if ($f->status_bayar === 'lunas')
                                            <span class="badge badge-lunas">
                                                <i class="fa-solid fa-check"></i> Lunas
                                            </span>
                                        @else
                                            <span class="badge badge-tempo">
                                                <i class="fa-solid fa-clock"></i> Tempo
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-btns-cell">
                                            <button type="button" class="btn-icon danger"
                                                    title="Hapus Faktur"
                                                    onclick="event.stopPropagation(); hapusFaktur({{ $f->id_pembelian }}, '{{ $f->nomor_pembelian }}', '{{ $f->status_bayar }}')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i class="fa-solid fa-truck-fast"></i>
                                            Belum ada faktur pasokan.<br>
                                            Klik "Catat Pasokan Baru" untuk mulai.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-card-footer">
                    Menampilkan <strong id="visibleCount">{{ $faktur->count() }}</strong>
                    dari <strong>{{ $faktur->count() }}</strong> faktur
                </div>
            </section>
        </div>

        {{-- KANAN: Side Panel Detail --}}
        <aside class="supplier-side">
            <div class="supplier-side-header">
                <div class="supplier-side-title">Detail Faktur Pasokan</div>
            </div>

            <div class="supplier-side-body" id="sideBody">
                <div class="supplier-empty-side" id="sideEmpty">
                    <i class="fa-solid fa-file-invoice"></i>
                    <div class="title">Belum Ada Faktur Dipilih</div>
                    <div class="sub">Klik salah satu baris faktur di sebelah kiri untuk melihat rincian barang yang masuk.</div>
                </div>

                <div id="sideDetail" style="display: none;">
                    <div class="faktur-info-card">
                        <div class="faktur-info-nomor" id="sideNomor">—</div>
                        <div class="faktur-info-supplier" id="sideSupplier">—</div>
                        <div class="faktur-info-meta" id="sideMeta">—</div>
                    </div>

                    <div style="font-size: 11px; font-weight: 800; color: #0f172a; margin-bottom: 8px;">
                        Rincian Barang Masuk:
                    </div>
                    <div class="faktur-item-list" id="sideItems"></div>

                    <div class="faktur-total-box">
                        <div class="faktur-total-label">TOTAL FAKTUR</div>
                        <div class="faktur-total-value" id="sideTotal">Rp 0</div>
                    </div>
                </div>
            </div>

            <div class="supplier-side-footer">
                <button type="button" class="btn-bayar-faktur" id="btnBayarFaktur"
                        onclick="bayarFaktur()" disabled>
                    <i class="fa-solid fa-money-bill-wave"></i> Pelunasan Tempo
                </button>
            </div>
        </aside>

    </div>

    {{-- ═══ MODAL: TAMBAH SUPPLIER ═══ --}}
    <div class="modal-overlay" id="modalSupplier">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fa-solid fa-truck"></i> Tambah Supplier Baru</h3>
                <button type="button" class="btn-close-modal" onclick="closeModalSupplier()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('supplier.storeSupplier') }}"
                  style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
                @csrf

                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Supplier / Distributor <span class="required">*</span></label>
                        <input type="text" name="nama_supplier" value="{{ old('nama_supplier') }}"
                               placeholder="Misal: CV Sumber Rejeki" required>
                        @error('nama_supplier')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-grid-2col">
                        <div class="form-group">
                            <label>Nama Sales</label>
                            <input type="text" name="nama_sales" value="{{ old('nama_sales') }}"
                                   placeholder="Misal: Pak Slamet">
                        </div>
                        <div class="form-group">
                            <label>No. WhatsApp / Telp</label>
                            <input type="text" name="no_telepon" value="{{ old('no_telepon') }}"
                                   placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Jadwal Kunjungan Rutin</label>
                        <input type="text" name="jadwal_kunjungan" value="{{ old('jadwal_kunjungan') }}"
                               placeholder="Misal: Setiap Selasa">
                    </div>

                    <div class="form-group">
                        <label>Alamat</label>
                        <input type="text" name="alamat" value="{{ old('alamat') }}"
                               placeholder="Misal: Jl. Diponegoro No. 45, Malang">
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                               placeholder="Misal: Supplier utama sembako">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalSupplier()">Batal</button>
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ MODAL: CATAT PASAKAN BARU ═══ --}}
    <div class="modal-overlay" id="modalPasokan">
        <div class="modal-card modal-card-pasokan">
            <div class="modal-header">
                <h3><i class="fa-solid fa-boxes-packing"></i> Catat Pasokan Barang Masuk</h3>
                <button type="button" class="btn-close-modal" onclick="closeModalPasokan()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('supplier.storePasokan') }}" id="formPasokan"
                style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
                @csrf

                {{-- Header info faktur --}}
                <div class="pasokan-header-form">
                    <div class="form-group">
                        <label>No. Faktur <span class="required">*</span></label>
                        <input type="text" name="nomor_pembelian"
                            value="{{ old('nomor_pembelian', 'PO-' . date('Ymd') . '-' . rand(100,999)) }}"
                            required>
                        @error('nomor_pembelian')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Supplier <span class="required">*</span></label>
                        <select name="id_supplier" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach ($supplierList as $s)
                                <option value="{{ $s->id_supplier }}"
                                    {{ old('id_supplier') == $s->id_supplier ? 'selected' : '' }}>
                                    {{ $s->nama_supplier }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status <span class="required">*</span></label>
                        <select name="status_bayar" id="statusBayar" onchange="toggleJatuhTempo()" required>
                            <option value="lunas">Lunas</option>
                            <option value="tempo">Tempo</option>
                        </select>
                    </div>

                    <div class="form-group" id="boxJatuhTempo" style="display: none;">
                        <label>Jatuh Tempo</label>
                        <input type="date" name="tanggal_jatuh_tempo"
                            value="{{ old('tanggal_jatuh_tempo', date('Y-m-d', strtotime('+14 days'))) }}">
                    </div>

                    <div class="form-group">
                        <label>Keterangan (Opsional)</label>
                        <input type="text" name="keterangan" value="{{ old('keterangan') }}"
                            placeholder="Misal: Restock sembako">
                    </div>
                </div>

                {{-- Split view --}}
                <div class="pasokan-split">

                    {{-- KIRI: Pilih produk --}}
                    <div class="pasokan-left">
                        <div class="pasokan-left-search">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="pasokanSearch"
                                placeholder="Cari nama produk / barcode..."
                                oninput="renderPasokanProdukList()">
                        </div>

                        <div class="pasokan-left-cats" id="pasokanCats">
                            <div class="cat-pill active" data-kat="Semua" onclick="setPasokanKategori('Semua', this)">Semua</div>
                            @foreach ($kategoriList as $kat)
                                <div class="cat-pill" data-kat="{{ $kat->nama_kategori }}"
                                    onclick="setPasokanKategori('{{ $kat->nama_kategori }}', this)">
                                    {{ $kat->nama_kategori }}
                                </div>
                            @endforeach
                        </div>

                        <div class="pasokan-left-list" id="pasokanProdukList"></div>

                        <div class="pasokan-left-footer">
                            <button type="button" class="btn-tambah-produk-baru" onclick="openModalProdukBaru()">
                                <i class="fa-solid fa-plus-circle"></i> Tambah Produk Baru
                            </button>
                        </div>
                    </div>

                    {{-- KANAN: Cart pasokan --}}
                    <div class="pasokan-right">
                        <div class="pasokan-right-header">
                            <span>Daftar Item dalam Faktur</span>
                            <span id="cartItemCount" style="color: #64748b; font-weight: 700;">0 item</span>
                        </div>

                        <div class="pasokan-cart-list" id="pasokanCartList"></div>

                        <div class="pasokan-right-footer">
                            <span class="label">TOTAL FAKTUR</span>
                            <span class="value" id="pasokanTotal">Rp 0</span>
                        </div>
                    </div>

                </div>

                {{-- Hidden input untuk items --}}
                <div id="pasokanItemsHidden"></div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalPasokan()">Batal</button>
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan & Tambah Stok
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ SUB-MODAL: TAMBAH PRODUK BARU ═══ --}}
    <div class="modal-overlay" id="modalProdukBaru" style="z-index: 1100;">
        <div class="modal-card">
            <div class="modal-header">
                <h3><i class="fa-solid fa-plus-circle"></i> Tambah Produk Baru</h3>
                <button type="button" class="btn-close-modal" onclick="closeModalProdukBaru()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="formProdukBaru" onsubmit="submitProdukBaru(event)"
                style="display: flex; flex-direction: column; flex: 1; min-height: 0;">

                <div class="modal-body">
                    <div class="form-grid-2col">
                        <div class="form-group">
                            <label>Barcode / SKU <span class="required">*</span></label>
                            <input type="text" name="barcode" id="pbBarcode" required>
                        </div>
                        <div class="form-group">
                            <label>Satuan <span class="required">*</span></label>
                            <select name="satuan" id="pbSatuan" required>
                                <option value="">-- Pilih --</option>
                                @foreach (['Pcs','Sak','Kg','Dus','Botol','Pouch','Kaleng','Bungkus','Pak','Box','Liter'] as $sat)
                                    <option value="{{ $sat }}">{{ $sat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nama Produk <span class="required">*</span></label>
                        <input type="text" name="nama_produk" id="pbNama" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori <span class="required">*</span></label>
                        <div id="pbKategoriExisting">
                            <select name="id_kategori" id="pbKategori">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($kategoriList as $kat)
                                    <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                            <div style="font-size: 10px; margin-top: 4px;">
                                <a href="#" onclick="event.preventDefault(); switchPBKategoriMode('new');"
                                style="color: #429198; font-weight: 700; text-decoration: none;">
                                    <i class="fa-solid fa-plus"></i> Atau tambah kategori baru
                                </a>
                            </div>
                        </div>
                        <div id="pbKategoriNew" style="display: none;">
                            <input type="text" name="new_kategori" id="pbKategoriBaru"
                                placeholder="Misal: Frozen Food">
                            <div style="font-size: 10px; margin-top: 4px;">
                                <a href="#" onclick="event.preventDefault(); switchPBKategoriMode('existing');"
                                style="color: #64748b; font-weight: 700; text-decoration: none;">
                                    <i class="fa-solid fa-arrow-left"></i> Kembali ke daftar
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid-2col">
                        <div class="form-group">
                            <label>Harga Beli <span class="required">*</span></label>
                            <input type="number" name="harga_beli" id="pbHargaBeli" min="0" step="any" required>
                        </div>
                        <div class="form-group">
                            <label>Harga Jual <span class="required">*</span></label>
                            <input type="number" name="harga_jual" id="pbHargaJual" min="0" step="any" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Batas Min. Stok <span class="required">*</span></label>
                        <input type="number" name="min_stok" id="pbMinStok" min="0" value="10" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalProdukBaru()">Batal</button>
                    <button type="submit" class="btn-save" id="btnSimpanPB">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan & Tambah ke Faktur
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
@php
    $produkListData = $produkList->map(function ($p) {
        return [
            'id'     => $p->id_produk,
            'nama'   => $p->nama_produk,
            'harga'  => (float) $p->harga_beli,
            'satuan' => $p->satuan,
        ];
    })->values()->toArray();
@endphp
@push('scripts')
@php
    $produkListData = $produkList->map(function ($p) {
        return [
            'id'       => $p->id_produk,
            'nama'     => $p->nama_produk,
            'barcode'  => $p->barcode,
            'kategori' => $p->kategori->nama_kategori ?? 'Tanpa Kategori',
            'satuan'   => $p->satuan,
            'harga'    => (float) $p->harga_beli,
        ];
    })->values()->toArray();
@endphp

@push('scripts')
<script>
    // ═══ STATE ═══
    const csrfToken = document.querySelector('meta[name=csrf-token]').content;
    let produkPool = @json($produkListData);
    let pasokanCart = []; // { id, nama, barcode, satuan, kategori, qty, harga }
    let pasokanKategori = 'Semua';
    let currentFakturId = null;
    let currentFakturStatus = null;

    // ═══ FILTER TABEL FAKTUR ═══
    function filterFaktur() {
        const keyword = document.getElementById('searchInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('#fakturTbody tr[data-search]');
        let visible = 0;
        rows.forEach(row => {
            const match = keyword === '' || (row.dataset.search || '').includes(keyword);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        document.getElementById('visibleCount').textContent = visible;
    }

    // ═══ MODAL SUPPLIER ═══
    function openModalSupplier()  { document.getElementById('modalSupplier').classList.add('active'); }
    function closeModalSupplier() { document.getElementById('modalSupplier').classList.remove('active'); }

    @if ($errors->has('nama_supplier'))
        document.addEventListener('DOMContentLoaded', () => openModalSupplier());
    @endif
    @if ($errors->hasAny(['nomor_pembelian', 'id_supplier', 'items', 'status_bayar']))
        document.addEventListener('DOMContentLoaded', () => openModalPasokan());
    @endif

    // ═══ MODAL PASAKAN ═══
    function openModalPasokan() {
        // Reset state
        pasokanCart = [];
        pasokanKategori = 'Semua';
        document.getElementById('pasokanSearch').value = '';
        document.querySelectorAll('#pasokanCats .cat-pill').forEach((el, i) => {
            el.classList.toggle('active', i === 0);
        });
        renderPasokanProdukList();
        renderPasokanCart();
        toggleJatuhTempo();
        document.getElementById('modalPasokan').classList.add('active');
    }

    function closeModalPasokan() {
        document.getElementById('modalPasokan').classList.remove('active');
    }

    function toggleJatuhTempo() {
        const status = document.getElementById('statusBayar').value;
        document.getElementById('boxJatuhTempo').style.display = status === 'tempo' ? 'block' : 'none';
    }

    // ═══ PANEL KIRI: LIST PRODUK ═══
    function setPasokanKategori(kat, el) {
        pasokanKategori = kat;
        document.querySelectorAll('#pasokanCats .cat-pill').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        renderPasokanProdukList();
    }

    function renderPasokanProdukList() {
        const keyword = document.getElementById('pasokanSearch').value.toLowerCase().trim();
        const list = document.getElementById('pasokanProdukList');

        const filtered = produkPool.filter(p => {
            const matchKat = pasokanKategori === 'Semua' || p.kategori === pasokanKategori;
            const haystack = `${p.nama} ${p.barcode || ''}`.toLowerCase();
            const matchSearch = keyword === '' || haystack.includes(keyword);
            return matchKat && matchSearch;
        });

        if (filtered.length === 0) {
            list.innerHTML = `
                <div style="text-align:center;padding:40px 20px;color:#94a3b8;font-size:11px;">
                    <i class="fa-solid fa-box-open" style="font-size:28px;display:block;margin-bottom:8px;color:#cbd5e1;"></i>
                    Produk tidak ditemukan
                </div>`;
            return;
        }

        list.innerHTML = filtered.map(p => {
            const inCart = pasokanCart.find(c => c.id === p.id);
            const cartQty = inCart ? inCart.qty : 0;
            return `
                <div class="pasokan-produk-item ${inCart ? 'in-cart' : ''}"
                     onclick="addToPasokanCart(${p.id})">
                    <div style="min-width:0;flex:1;">
                        <div class="pasokan-produk-nama">${p.nama}</div>
                        <div class="pasokan-produk-meta">
                            ${p.barcode || '-'} • ${p.satuan}
                            ${cartQty > 0 ? ` <span style="color:#16a34a;font-weight:800;">• ${cartQty} di faktur</span>` : ''}
                        </div>
                    </div>
                    <div class="pasokan-produk-harga">Rp ${p.harga.toLocaleString('id-ID')}</div>
                </div>
            `;
        }).join('');
    }

    // ═══ PANEL KANAN: CART ═══
    function addToPasokanCart(id) {
        const prod = produkPool.find(p => p.id === id);
        if (!prod) return;

        const existing = pasokanCart.find(c => c.id === id);
        if (existing) {
            existing.qty += 1;
        } else {
            pasokanCart.push({
                id:       prod.id,
                nama:     prod.nama,
                barcode:  prod.barcode,
                satuan:   prod.satuan,
                kategori: prod.kategori,
                qty:      1,
                harga:    prod.harga,
            });
        }

        renderPasokanProdukList();
        renderPasokanCart();
    }

    function removeFromPasokanCart(id) {
        pasokanCart = pasokanCart.filter(c => c.id !== id);
        renderPasokanProdukList();
        renderPasokanCart();
    }

    function updateCartQty(id, val) {
        const item = pasokanCart.find(c => c.id === id);
        if (!item) return;
        item.qty = Math.max(1, parseFloat(val) || 1);
        updateCartSubtotal(id);
        renderPasokanProdukList();
    }

    function updateCartHarga(id, val) {
        const item = pasokanCart.find(c => c.id === id);
        if (!item) return;
        item.harga = Math.max(0, parseFloat(val) || 0);
        updateCartSubtotal(id);
    }

    function updateCartSubtotal(id) {
        const item = pasokanCart.find(c => c.id === id);
        if (!item) return;
        const sub = item.qty * item.harga;
        const subEl = document.getElementById(`sub_${id}`);
        if (subEl) subEl.textContent = 'Rp ' + sub.toLocaleString('id-ID');
        hitungTotalPasokan();
    }

    function hitungTotalPasokan() {
        const total = pasokanCart.reduce((sum, c) => sum + (c.qty * c.harga), 0);
        document.getElementById('pasokanTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    function renderPasokanCart() {
        const list = document.getElementById('pasokanCartList');
        const count = document.getElementById('cartItemCount');

        count.textContent = pasokanCart.length + ' item';

        if (pasokanCart.length === 0) {
            list.innerHTML = `
                <div class="pasokan-cart-empty">
                    <i class="fa-solid fa-cart-plus"></i>
                    <p>Belum ada produk dipilih</p>
                    <div style="font-size:10px;max-width:220px;line-height:1.5;">
                        Klik produk di panel kiri untuk menambahkan ke faktur.
                    </div>
                </div>`;
            hitungTotalPasokan();
            return;
        }

        list.innerHTML = pasokanCart.map(item => `
            <div class="pasokan-cart-item">
                <div class="pasokan-cart-item-header">
                    <div style="min-width:0;flex:1;">
                        <div class="pasokan-cart-item-nama">${item.nama}</div>
                        <div class="pasokan-cart-item-meta">${item.barcode || '-'} • ${item.satuan}</div>
                    </div>
                    <button type="button" class="pasokan-cart-item-remove"
                            onclick="removeFromPasokanCart(${item.id})">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
                <div class="pasokan-cart-item-inputs">
                    <div>
                        <label>Qty</label>
                        <input type="number" min="1" step="any" value="${item.qty}"
                               onchange="updateCartQty(${item.id}, this.value)">
                    </div>
                    <div>
                        <label>Harga Beli</label>
                        <input type="number" min="0" step="any" value="${item.harga}"
                               onchange="updateCartHarga(${item.id}, this.value)">
                    </div>
                    <div class="pasokan-cart-item-sub">
                        <span class="label">Subtotal</span>
                        <span class="value" id="sub_${item.id}">
                            Rp ${(item.qty * item.harga).toLocaleString('id-ID')}
                        </span>
                    </div>
                </div>
            </div>
        `).join('');

        hitungTotalPasokan();
    }

    // ═══ SUB-MODAL: TAMBAH PRODUK BARU ═══
    function openModalProdukBaru() {
        document.getElementById('formProdukBaru').reset();
        switchPBKategoriMode('existing');
        document.getElementById('modalProdukBaru').classList.add('active');
        setTimeout(() => document.getElementById('pbBarcode').focus(), 100);
    }
    function closeModalProdukBaru() {
        document.getElementById('modalProdukBaru').classList.remove('active');
    }

    function switchPBKategoriMode(mode) {
        const ex = document.getElementById('pbKategoriExisting');
        const nw = document.getElementById('pbKategoriNew');
        if (mode === 'new') {
            ex.style.display = 'none'; nw.style.display = 'block';
            document.getElementById('pbKategori').value = '';
            document.getElementById('pbKategoriBaru').focus();
        } else {
            ex.style.display = 'block'; nw.style.display = 'none';
            document.getElementById('pbKategoriBaru').value = '';
        }
    }

    async function submitProdukBaru(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSimpanPB');
        const form = document.getElementById('formProdukBaru');
        const formData = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        try {
            const res = await fetch('{{ route('supplier.storeProdukBaru') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await res.json();

            if (res.ok && data.success) {
                // Push ke pool & auto-add ke cart
                produkPool.push(data.produk);
                produkPool.sort((a, b) => a.nama.localeCompare(b.nama));
                addToPasokanCart(data.produk.id);

                // Update dropdown kategori di sub-modal
                const selectKat = document.getElementById('pbKategori');
                if (data.kategori_list) {
                    selectKat.innerHTML = '<option value="">-- Pilih Kategori --</option>' +
                        data.kategori_list.map(k =>
                            `<option value="${k.id_kategori}">${k.nama_kategori}</option>`
                        ).join('');
                }

                closeModalProdukBaru();
                showToast(data.message, 'success');

                // Auto-set produk baru di cart dengan qty 1
                const item = pasokanCart.find(c => c.id === data.produk.id);
                if (item) item.harga = data.produk.harga;

                renderPasokanCart();
            } else {
                const err = data.message
                    || Object.values(data.errors || {})[0]?.[0]
                    || 'Gagal menyimpan produk.';
                showToast(err, 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Terjadi kesalahan jaringan.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan & Tambah ke Faktur';
        }
    }

    // ═══ SUBMIT PASAKAN — Serialize Cart ke Input Hidden ═══
    document.getElementById('formPasokan').addEventListener('submit', function (e) {
        if (pasokanCart.length === 0) {
            e.preventDefault();
            showToast('Minimal 1 item barang dalam faktur.', 'error');
            return;
        }

        // Hapus hidden input lama
        const container = document.getElementById('pasokanItemsHidden');
        container.innerHTML = '';

        // Buat hidden input untuk setiap item
        pasokanCart.forEach((item, idx) => {
            const inputs = [
                ['items[' + idx + '][id_produk]',  item.id],
                ['items[' + idx + '][jumlah]',     item.qty],
                ['items[' + idx + '][harga_beli]', item.harga],
            ];
            inputs.forEach(([name, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                container.appendChild(input);
            });
        });
    });

    // ═══ SIDE PANEL DETAIL FAKTUR ═══
    async function selectFaktur(id, rowEl) {
        currentFakturId = id;
        document.querySelectorAll('#fakturTbody tr').forEach(r => r.style.background = '');
        rowEl.style.background = '#e0f2fe';

        document.getElementById('sideEmpty').style.display = 'none';
        document.getElementById('sideDetail').style.display = 'block';
        document.getElementById('sideItems').innerHTML =
            '<div style="text-align:center;color:#94a3b8;padding:12px;"><i class="fa-solid fa-spinner fa-spin"></i></div>';
        document.getElementById('sideTotal').textContent = 'Rp 0';
        document.getElementById('btnBayarFaktur').disabled = true;

        try {
            const res = await fetch(`/supplier/pasokan/${id}/detail`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            const f = data.faktur;
            currentFakturStatus = f.status_bayar;

            document.getElementById('sideNomor').textContent = f.nomor_pembelian;
            document.getElementById('sideSupplier').textContent =
                `${f.nama_supplier}${f.nama_sales !== '-' ? ' — ' + f.nama_sales : ''}`;
            document.getElementById('sideMeta').innerHTML = `
                <div>📅 ${f.tanggal} • Penerima: ${f.nama_penerima}</div>
                ${f.keterangan ? `<div>📝 ${f.keterangan}</div>` : ''}
                ${f.tanggal_jatuh_tempo ? `<div>⏰ Jatuh tempo: ${f.tanggal_jatuh_tempo}</div>` : ''}
                <div>Status: <strong style="color: ${f.status_bayar === 'lunas' ? '#16a34a' : '#dc2626'};">${f.status_bayar.toUpperCase()}</strong></div>
            `;

            document.getElementById('sideItems').innerHTML = data.items.map(it => `
                <div class="faktur-item">
                    <div style="min-width:0;flex:1;">
                        <div class="faktur-item-nama">${it.nama_produk}</div>
                        <div class="faktur-item-qty">${it.jumlah} ${it.satuan} × Rp ${it.harga_beli.toLocaleString('id-ID')}</div>
                    </div>
                    <div class="faktur-item-subtotal">Rp ${it.subtotal.toLocaleString('id-ID')}</div>
                </div>
            `).join('') || '<div style="text-align:center;color:#94a3b8;padding:12px;font-size:11px;">Tidak ada item</div>';

            document.getElementById('sideTotal').textContent = 'Rp ' + f.total_pembelian.toLocaleString('id-ID');
            document.getElementById('btnBayarFaktur').disabled = f.status_bayar !== 'tempo';

        } catch (err) {
            console.error(err);
            document.getElementById('sideItems').innerHTML =
                '<div style="color:#dc2626;text-align:center;padding:12px;font-size:11px;">Gagal memuat data.</div>';
        }
    }

    function bayarFaktur() {
        if (!currentFakturId || currentFakturStatus !== 'tempo') return;
        if (!confirm('Yakin ingin melunasi faktur ini?')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/supplier/pasokan/${currentFakturId}/bayar`;
        form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">`;
        document.body.appendChild(form);
        form.submit();
    }

    function hapusFaktur(id, nomor, status) {
        if (status === 'lunas') {
            alert(`Faktur "${nomor}" sudah lunas. Tidak bisa dihapus.`);
            return;
        }
        if (!confirm(`Yakin hapus faktur "${nomor}"? Stok barang akan dikembalikan.`)) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/supplier/pasokan/${id}`;
        form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}"><input type="hidden" name="_method" value="DELETE">`;
        document.body.appendChild(form);
        form.submit();
    }

    // ═══ EVENT LISTENERS ═══
    document.getElementById('modalSupplier').addEventListener('click', function (e) {
        if (e.target === this) closeModalSupplier();
    });
    document.getElementById('modalPasokan').addEventListener('click', function (e) {
        if (e.target === this) closeModalPasokan();
    });
    document.getElementById('modalProdukBaru').addEventListener('click', function (e) {
        if (e.target === this) closeModalProdukBaru();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (document.getElementById('modalProdukBaru').classList.contains('active')) {
                closeModalProdukBaru();
            } else {
                closeModalSupplier();
                closeModalPasokan();
            }
        }
    });
</script>
@endpush