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

        <button type="button" class="btn-primary" onclick="openModalTambah()">
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

{{-- ═══════════════════════════════════════════
     MODAL: TAMBAH PRODUK BARU
     ═══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalTambahProduk">
    <div class="modal-card">

        {{-- Header --}}
        <div class="modal-header">
            <h3>Tambah Barang Baru</h3>
            <button type="button" class="btn-close-modal" onclick="closeModalTambah()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST"
              action="{{ route('produk.store') }}"
              enctype="multipart/form-data"
              id="formTambahProduk"
              style="display: flex; flex-direction: column; flex: 1; min-height: 0;">

            @csrf

            {{-- Body --}}
            <div class="modal-body">

                {{-- Upload Foto Produk (Dropzone) --}}
                <div class="form-group">
                    <label>Foto Produk (Tampil di POS Kasir)</label>
                    <div class="image-upload-box" id="fotoDropzone">
                        <i class="fa-solid fa-cloud-arrow-up" id="fotoIcon"></i>
                        <span id="fotoText">Klik atau Tarik Foto Produk ke Sini</span>
                        <small id="fotoHint">Format: JPG, PNG, WEBP (Maks. 2MB) — otomatis dikompres ke WebP</small>
                        <img id="fotoPreviewImg" src="" alt="Preview" style="display: none;">
                        <input type="file"
                               name="foto_produk"
                               id="fotoInput"
                               accept="image/jpeg,image/jpg,image/png,image/webp"
                               class="file-input-hidden"
                               onchange="previewFoto(this)">
                    </div>
                    @error('foto_produk')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Row 1: Barcode + Kategori --}}
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label>Kode / SKU Barcode <span class="required">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fa-solid fa-barcode"></i>
                            <input type="text"
                                   name="barcode"
                                   value="{{ old('barcode') }}"
                                   placeholder="Contoh: BRS-006"
                                   class="@error('barcode') error @enderror"
                                   required>
                        </div>
                        @error('barcode')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Kategori Produk <span class="required">*</span></label>

                        {{-- Mode 1: Dropdown existing --}}
                        <div id="kategoriModeExisting">
                            <select name="id_kategori"
                                    id="selectKategori"
                                    class="@error('id_kategori') error @enderror">
                                <option value="">Pilih Kategori...</option>
                                @foreach ($kategoriList as $kat)
                                    <option value="{{ $kat->id_kategori }}"
                                        {{ old('id_kategori') == $kat->id_kategori ? 'selected' : '' }}>
                                        {{ $kat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_kategori')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            <div style="font-size: 10px; margin-top: 4px;">
                                <a href="#" onclick="switchKategoriMode('new'); return false;"
                                   style="color: #429198; font-weight: 700; text-decoration: none;">
                                    <i class="fa-solid fa-plus"></i> Atau tambah kategori baru
                                </a>
                            </div>
                        </div>

                        {{-- Mode 2: Input kategori baru --}}
                        <div id="kategoriModeNew" style="display: none;">
                            <input type="text"
                                   name="new_kategori"
                                   id="inputKategoriBaru"
                                   value="{{ old('new_kategori') }}"
                                   placeholder="Misal: Frozen Food"
                                   class="@error('new_kategori') error @enderror">
                            @error('new_kategori')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            <div style="font-size: 10px; margin-top: 4px;">
                                <a href="#" onclick="switchKategoriMode('existing'); return false;"
                                   style="color: #64748b; font-weight: 700; text-decoration: none;">
                                    <i class="fa-solid fa-arrow-left"></i> Kembali pilih dari daftar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Nama Produk --}}
                <div class="form-group">
                    <label>Nama Produk Lengkap <span class="required">*</span></label>
                    <input type="text"
                           name="nama_produk"
                           value="{{ old('nama_produk') }}"
                           placeholder="Masukkan nama produk..."
                           class="@error('nama_produk') error @enderror"
                           required>
                    @error('nama_produk')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Row 3: Harga Beli + Harga Jual --}}
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label>Harga Beli / Modal (Rp) <span class="required">*</span></label>
                        <input type="number"
                               name="harga_beli"
                               value="{{ old('harga_beli') }}"
                               placeholder="0"
                               min="0"
                               step="100"
                               class="@error('harga_beli') error @enderror"
                               required>
                        @error('harga_beli')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Harga Jual Kasir (Rp) <span class="required">*</span></label>
                        <input type="number"
                               name="harga_jual"
                               value="{{ old('harga_jual') }}"
                               placeholder="0"
                               min="0"
                               step="100"
                               class="@error('harga_jual') error @enderror"
                               required>
                        @error('harga_jual')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Row 4: Stok Awal + Satuan --}}
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label>Jumlah Stok Awal <span class="required">*</span></label>
                        <input type="number"
                               name="stok_sekarang"
                               value="{{ old('stok_sekarang', 0) }}"
                               placeholder="0"
                               min="0"
                               class="@error('stok_sekarang') error @enderror"
                               required>
                        @error('stok_sekarang')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Satuan Barang <span class="required">*</span></label>
                        <select name="satuan" class="@error('satuan') error @enderror" required>
                            <option value="">Pilih Satuan...</option>
                            @php
                                $satuanList = ['Pcs', 'Sak', 'Kg', 'Dus', 'Botol', 'Pouch', 'Kaleng', 'Bungkus', 'Pak', 'Box', 'Liter'];
                            @endphp
                            @foreach ($satuanList as $sat)
                                <option value="{{ $sat }}" {{ old('satuan') == $sat ? 'selected' : '' }}>
                                    {{ $sat }}
                                </option>
                            @endforeach
                        </select>
                        @error('satuan')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Row 5: Batas Minimal Stok --}}
                <div class="form-group">
                    <label>Batas Minimal Stok (Peringatan Restock) <span class="required">*</span></label>
                    <input type="number"
                           name="min_stok"
                           value="{{ old('min_stok', 5) }}"
                           placeholder="Contoh: 5"
                           min="0"
                           class="@error('min_stok') error @enderror"
                           required>
                    <div style="font-size: 10px; color: #94a3b8; margin-top: 2px;">
                        Sistem akan memperingatkan jika stok mencapai angka ini.
                    </div>
                    @error('min_stok')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModalTambah()">
                    Batal
                </button>
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Produk
                </button>
            </div>

        </form>
    </div>
</div>

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

    // ═══════════════════════════════════════════
    // MODAL TAMBAH PRODUK
    // ═══════════════════════════════════════════
    function openModalTambah() {
        document.getElementById('modalTambahProduk').classList.add('active');
    }

    function closeModalTambah() {
        document.getElementById('modalTambahProduk').classList.remove('active');
    }

    // Tutup modal kalau klik area overlay (luar card)
    document.getElementById('modalTambahProduk').addEventListener('click', function (e) {
        if (e.target === this) {
            closeModalTambah();
        }
    });

    // Tutup modal dengan tombol ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModalTambah();
        }
    });

    // AUTO-OPEN modal kalau ada validation error dari server
    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            openModalTambah();
        });
    @endif

    // ═══════════════════════════════════════════
    // PREVIEW FOTO + DRAG & DROP
    // ═══════════════════════════════════════════
    function previewFoto(input) {
        const preview = document.getElementById('fotoPreviewImg');
        const icon    = document.getElementById('fotoIcon');
        const text    = document.getElementById('fotoText');
        const hint    = document.getElementById('fotoHint');
        const box     = document.getElementById('fotoDropzone');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                icon.style.display = 'none';
                text.textContent = input.files[0].name;
                text.style.color = '#16a34a';
                hint.textContent = '✓ Foto siap diupload — otomatis dikompres ke WebP';
                hint.style.color = '#16a34a';
                box.style.borderColor = '#16a34a';
                box.style.backgroundColor = '#f0fdf4';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            resetFotoDropzone();
        }
    }

    function resetFotoDropzone() {
        const preview = document.getElementById('fotoPreviewImg');
        const icon    = document.getElementById('fotoIcon');
        const text    = document.getElementById('fotoText');
        const hint    = document.getElementById('fotoHint');
        const box     = document.getElementById('fotoDropzone');

        preview.src = '';
        preview.style.display = 'none';
        icon.style.display = 'block';
        text.textContent = 'Klik atau Tarik Foto Produk ke Sini';
        text.style.color = '#475569';
        hint.textContent = 'Format: JPG, PNG, WEBP (Maks. 2MB) — otomatis dikompres ke WebP';
        hint.style.color = '#94a3b8';
        box.style.borderColor = '#cbd5e1';
        box.style.backgroundColor = '#f8fafc';
    }

    // Drag & drop support
    document.addEventListener('DOMContentLoaded', function () {
        const dropzone = document.getElementById('fotoDropzone');
        if (!dropzone) return;

        ['dragenter', 'dragover'].forEach(evt => {
            dropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                dropzone.style.borderColor = '#429198';
                dropzone.style.backgroundColor = '#f0fdfa';
            });
        });

        ['dragleave', 'drop'].forEach(evt => {
            dropzone.addEventListener(evt, (e) => {
                e.preventDefault();
                if (!document.getElementById('fotoInput').files.length) {
                    dropzone.style.borderColor = '#cbd5e1';
                    dropzone.style.backgroundColor = '#f8fafc';
                }
            });
        });

        dropzone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const input = document.getElementById('fotoInput');
                input.files = files;
                previewFoto(input);
            }
        });
    });

    // Reset dropzone setiap modal ditutup
    function closeModalTambah() {
        document.getElementById('modalTambahProduk').classList.remove('active');
        resetFotoDropzone();
        document.getElementById('formTambahProduk').reset();
    }

    // AUTO-OPEN modal kalau ada validation error dari server
    @if ($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('modalTambahProduk').classList.add('active');
        });
    @endif

    // AUTO-SWITCH ke mode "new" kalau ada error validasi di new_kategori
    @if ($errors->has('new_kategori'))
        document.addEventListener('DOMContentLoaded', function () {
            switchKategoriMode('new');
        });
    @endif

    // ═══════════════════════════════════════════
    // TOGGLE MODE KATEGORI (existing vs new)
    // ═══════════════════════════════════════════
    function switchKategoriMode(mode) {
        const existing = document.getElementById('kategoriModeExisting');
        const newMode  = document.getElementById('kategoriModeNew');
        const selectEl = document.getElementById('selectKategori');
        const inputEl  = document.getElementById('inputKategoriBaru');

        if (mode === 'new') {
            existing.style.display = 'none';
            newMode.style.display  = 'block';
            selectEl.value = '';       // reset dropdown biar tidak bentrok validasi
            inputEl.focus();
        } else {
            existing.style.display = 'block';
            newMode.style.display  = 'none';
            inputEl.value = '';        // reset input kategori baru
        }
    }

    // AUTO-SWITCH ke mode "new" kalau ada error validasi di new_kategori
    @if ($errors->has('new_kategori'))
        document.addEventListener('DOMContentLoaded', function () {
            switchKategoriMode('new');
        });
    @endif
</script>
@endpush