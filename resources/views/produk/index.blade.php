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

    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <button type="button" class="btn-secondary" onclick="openModalKategori()">
            <i class="fa-solid fa-tags"></i> Kelola Kategori
        </button>
        <button type="button" class="btn-secondary" onclick="openModalPenyesuaian()">
            <i class="fa-solid fa-clipboard-check"></i> Penyesuaian Stok
        </button>
        <button type="button" class="btn-primary" onclick="openModalTambah()">
            <i class="fa-solid fa-plus"></i> Tambah Barang Baru
        </button>
    </div>
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
                                        data-produk="{{ json_encode([
                                            'id_produk'     => $p->id_produk,
                                            'barcode'       => $p->barcode,
                                            'nama_produk'   => $p->nama_produk,
                                            'id_kategori'   => $p->id_kategori,
                                            'satuan'        => $p->satuan,
                                            'harga_beli'    => $p->harga_beli,
                                            'harga_jual'    => $p->harga_jual,
                                            'stok_sekarang' => $p->stok_sekarang,
                                            'min_stok'      => $p->min_stok,
                                            'foto_produk'   => $p->foto_produk,
                                        ]) }}"
                                        onclick="openEditModal(this)">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn-icon danger" title="Hapus Produk"
                                        data-id="{{ $p->id_produk }}"
                                        data-nama="{{ $p->nama_produk }}"
                                        onclick="openDeleteModal(this)">
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

{{-- ═══════════════════════════════════════════
     MODAL: EDIT PRODUK
     ═══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalEditProduk">
    <div class="modal-card">

        <div class="modal-header">
            <h3><i class="fa-solid fa-pen-to-square"></i> Edit Produk</h3>
            <button type="button" class="btn-close-modal" onclick="closeEditModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST"
              action=""
              enctype="multipart/form-data"
              id="formEditProduk"
              style="display: flex; flex-direction: column; flex: 1; min-height: 0;">

            @csrf
            @method('PUT')

            <div class="modal-body">

                {{-- Upload Foto Produk --}}
                <div class="form-group">
                    <label>Foto Produk (Tampil di POS Kasir)</label>
                    <div class="image-upload-box" id="editFotoDropzone">
                        <i class="fa-solid fa-cloud-arrow-up" id="editFotoIcon"></i>
                        <span id="editFotoText">Klik atau Tarik Foto Baru ke Sini</span>
                        <small id="editFotoHint">Biarkan kosong jika tidak ingin ganti foto</small>
                        <img id="editFotoPreviewImg" src="" alt="Preview" style="display: none;">
                        <input type="file"
                               name="foto_produk"
                               id="editFotoInput"
                               accept="image/jpeg,image/jpg,image/png,image/webp"
                               class="file-input-hidden"
                               onchange="previewFotoEdit(this)">
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
                                   id="editBarcode"
                                   placeholder="Contoh: BRS-006"
                                   required>
                        </div>
                        @error('barcode')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Kategori Produk <span class="required">*</span></label>

                        {{-- Mode 1: Dropdown --}}
                        <div id="editKategoriModeExisting">
                            <select name="id_kategori" id="editSelectKategori">
                                <option value="">Pilih Kategori...</option>
                                @foreach ($kategoriList as $kat)
                                    <option value="{{ $kat->id_kategori }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                            <div style="font-size: 10px; margin-top: 4px;">
                                <a href="#" onclick="switchKategoriModeEdit('new'); return false;"
                                   style="color: #429198; font-weight: 700; text-decoration: none;">
                                    <i class="fa-solid fa-plus"></i> Atau tambah kategori baru
                                </a>
                            </div>
                        </div>

                        {{-- Mode 2: Kategori Baru --}}
                        <div id="editKategoriModeNew" style="display: none;">
                            <input type="text"
                                   name="new_kategori"
                                   id="editInputKategoriBaru"
                                   placeholder="Misal: Frozen Food">
                            <div style="font-size: 10px; margin-top: 4px;">
                                <a href="#" onclick="switchKategoriModeEdit('existing'); return false;"
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
                    <input type="text" name="nama_produk" id="editNamaProduk" placeholder="Masukkan nama produk..." required>
                </div>

                {{-- Row 3: Harga Beli + Harga Jual --}}
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label>Harga Beli / Modal (Rp) <span class="required">*</span></label>
                        <input type="number" name="harga_beli" id="editHargaBeli" placeholder="0" min="0" step="100" required>
                    </div>
                    <div class="form-group">
                        <label>Harga Jual Kasir (Rp) <span class="required">*</span></label>
                        <input type="number" name="harga_jual" id="editHargaJual" placeholder="0" min="0" step="100" required>
                    </div>
                </div>

                {{-- Row 4: Stok + Satuan --}}
                <div class="form-grid-2col">
                    <div class="form-group">
                        <label>Jumlah Stok <span class="required">*</span></label>
                        <input type="number" name="stok_sekarang" id="editStok" placeholder="0" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Satuan Barang <span class="required">*</span></label>
                        <select name="satuan" id="editSatuan" required>
                            <option value="">Pilih Satuan...</option>
                            @php
                                $satuanList = ['Pcs', 'Sak', 'Kg', 'Dus', 'Botol', 'Pouch', 'Kaleng', 'Bungkus', 'Pak', 'Box', 'Liter'];
                            @endphp
                            @foreach ($satuanList as $sat)
                                <option value="{{ $sat }}">{{ $sat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Row 5: Min Stok --}}
                <div class="form-group">
                    <label>Batas Minimal Stok <span class="required">*</span></label>
                    <input type="number" name="min_stok" id="editMinStok" placeholder="5" min="0" required>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Batal</button>
                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                </button>
            </div>

        </form>
    </div>
</div>

{{-- ═══════════════════════════════════════════
     MODAL: KONFIRMASI HAPUS PRODUK
     ═══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalHapusProduk">
    <div class="modal-card modal-card-sm">

        <div class="modal-header">
            <h3 style="color: #dc2626;">
                <i class="fa-solid fa-triangle-exclamation"></i> Konfirmasi Hapus
            </h3>
            <button type="button" class="btn-close-modal" onclick="closeDeleteModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="modal-info-box warning">
                <p style="font-weight: 700; font-size: 12px; margin-bottom: 6px;">
                    Anda yakin ingin menghapus produk ini?
                </p>
                <p style="font-size: 11px;">
                    Produk: <strong id="deleteNamaProduk">-</strong>
                </p>
                <p style="font-size: 10px; margin-top: 8px; opacity: 0.8;">
                    Tindakan ini tidak bisa dibatalkan. Foto produk juga akan dihapus permanen.
                </p>
            </div>
        </div>

        <form method="POST" action="" id="formHapusProduk" style="display: contents;">
            @csrf
            @method('DELETE')

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Batal</button>
                <button type="submit" class="btn-danger">
                    <i class="fa-solid fa-trash"></i> Ya, Hapus
                </button>
            </div>
        </form>

    </div>
</div>


{{-- ═══════════════════════════════════════════
     MODAL: KELOLA KATEGORI
     ═══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalKelolaKategori">
    <div class="modal-card">

        <div class="modal-header">
            <h3><i class="fa-solid fa-tags"></i> Kelola Kategori Produk</h3>
            <button type="button" class="btn-close-modal" onclick="closeModalKategori()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">

            {{-- Form Tambah Kategori --}}
            <div>
                <label style="font-size: 11px; font-weight: 700; color: #0f172a; display: block; margin-bottom: 6px;">
                    Tambah Kategori Baru
                </label>
                <div class="kategori-add-form">
                    <input type="text"
                           id="inputKategoriKelola"
                           placeholder="Misal: Frozen Food"
                           maxlength="255"
                           onkeydown="if(event.key==='Enter'){ event.preventDefault(); submitKategoriTambah(); }">
                    <button type="button" class="btn-primary" id="btnTambahKategori"
                            onclick="submitKategoriTambah()"
                            style="padding: 10px 18px;">
                        <i class="fa-solid fa-plus"></i> Tambah
                    </button>
                </div>
            </div>

            {{-- Daftar Kategori --}}
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="font-size: 11px; font-weight: 700; color: #0f172a;">
                        Daftar Kategori
                    </label>
                    <span style="font-size: 10px; color: #64748b;">
                        Total: <strong id="kategoriCounter" style="color: #0f172a;">0</strong> kategori
                    </span>
                </div>

                <div class="kategori-table-wrap">
                    <table class="kategori-table">
                        <thead>
                            <tr>
                                <th>Nama Kategori</th>
                                <th style="text-align: center; width: 120px;">Jumlah Produk</th>
                                <th style="text-align: center; width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="kategoriTbody">
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 30px; color: #94a3b8;">
                                    <i class="fa-solid fa-spinner fa-spin"></i> Memuat...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModalKategori()">Tutup</button>
        </div>

    </div>
</div>

{{-- ═══════════════════════════════════════════
     SUB-MODAL: EDIT KATEGORI
     ═══════════════════════════════════════════ --}}
<div class="modal-overlay" id="subModalEditKategori">
    <div class="modal-card modal-card-sm">

        <div class="modal-header">
            <h3><i class="fa-solid fa-pen-to-square"></i> Edit Kategori</h3>
            <button type="button" class="btn-close-modal" onclick="closeSubModalEditKategori()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="form-group">
                <label>Nama Kategori <span class="required">*</span></label>
                <input type="text"
                       id="editKategoriNama"
                       maxlength="255"
                       onkeydown="if(event.key==='Enter'){ event.preventDefault(); submitKategoriEdit(); }">
                <input type="hidden" id="editKategoriId">
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeSubModalEditKategori()">Batal</button>
            <button type="button" class="btn-save" id="btnSimpanKategori" onclick="submitKategoriEdit()">
                <i class="fa-solid fa-floppy-disk"></i> Simpan
            </button>
        </div>

    </div>
</div>

{{-- ═══════════════════════════════════════════
     MODAL: PENYESUAIAN STOK (OPNAME) — SPLIT LAYOUT
     ═══════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalPenyesuaianStok">
    <div class="modal-card modal-card-lg">

        <div class="modal-header">
            <h3><i class="fa-solid fa-clipboard-check"></i> Penyesuaian Stok (Opname)</h3>
            <button type="button" class="btn-close-modal" onclick="closeModalPenyesuaian()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('produk.penyesuaian') }}" id="formPenyesuaianStok">
            @csrf
            <input type="hidden" name="id_produk" id="opnameSelectedProdukId">

            <div class="opname-layout">

                {{-- Panel KIRI: Pencarian & Daftar Produk --}}
                <div class="opname-left">
                    <div class="opname-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text"
                               id="opnameSearch"
                               placeholder="Cari nama produk / barcode..."
                               oninput="filterOpnameProducts()">
                    </div>

                    <div class="opname-cats" id="opnameCategories">
                        <div class="opname-cat active" data-kat="Semua" onclick="setOpnameKategori('Semua', this)">Semua</div>
                        @foreach ($kategoriList as $kat)
                            <div class="opname-cat"
                                 data-kat="{{ $kat->nama_kategori }}"
                                 onclick="setOpnameKategori('{{ $kat->nama_kategori }}', this)">
                                {{ $kat->nama_kategori }}
                            </div>
                        @endforeach
                    </div>

                    <div class="opname-list" id="opnameList">
                        {{-- Diisi oleh JavaScript --}}
                    </div>
                </div>

                {{-- Panel KANAN: Detail & Input --}}
                <div class="opname-right">

                    {{-- Empty state: belum pilih produk --}}
                    <div class="opname-empty" id="opnameEmptyState">
                        <i class="fa-solid fa-hand-pointer"></i>
                        <p>Pilih produk di sebelah kiri</p>
                        <div style="font-size: 10px; max-width: 260px;">
                            Cari berdasarkan nama atau filter per kategori, lalu klik produk untuk mulai penyesuaian.
                        </div>
                    </div>

                    {{-- Detail panel: setelah produk dipilih --}}
                    <div id="opnameDetailPanel" style="display: none;">

                        <div class="opname-detail-header">
                            <div class="opname-product-name" id="opnameDetailNama">—</div>
                            <div class="opname-product-meta" id="opnameDetailMeta">—</div>
                        </div>

                        <div class="opname-info-grid">
                            <div class="stok-display-box">
                                <div class="label">Stok Sistem</div>
                                <div class="value" id="opnameStokSistem">—</div>
                            </div>
                            <div class="stok-display-box">
                                <div class="label">Selisih</div>
                                <div class="value neutral" id="opnameSelisih">—</div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-top: 16px;">
                            <label>Stok Fisik Riil <span class="required">*</span></label>
                            <input type="number"
                                   name="stok_fisik"
                                   id="opnameStokFisik"
                                   min="0"
                                   placeholder="Masukkan hasil hitung fisik..."
                                   required
                                   oninput="hitungSelisih()">
                            <div style="font-size: 10px; color: #94a3b8; margin-top: 4px;">
                                <i class="fa-solid fa-info-circle"></i>
                                Selisih dihitung otomatis: <strong>Fisik − Sistem</strong>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModalPenyesuaian()">
                    Batal
                </button>
                <button type="submit" class="btn-save" id="opnameSubmitBtn" disabled>
                    <i class="fa-solid fa-check"></i> Eksekusi Penyesuaian
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@php
    $opnameProductsData = $produk->map(function ($p) {
        return [
            'id'       => $p->id_produk,
            'nama'     => $p->nama_produk,
            'barcode'  => $p->barcode,
            'kategori' => $p->kategori->nama_kategori ?? 'Tanpa Kategori',
            'satuan'   => $p->satuan,
            'stok'     => (int) $p->stok_sekarang,
        ];
    })->values()->toArray();
@endphp
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
    @if ($errors->hasAny(['barcode', 'nama_produk', 'satuan', 'harga_beli', 'harga_jual', 'stok_sekarang', 'min_stok', 'foto_produk', 'new_kategori']))
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

    // ═══════════════════════════════════════════
    // MODAL EDIT PRODUK
    // ═══════════════════════════════════════════
    function openEditModal(btn) {
        const p = JSON.parse(btn.dataset.produk);

        // Isi field form
        document.getElementById('editBarcode').value      = p.barcode || '';
        document.getElementById('editNamaProduk').value   = p.nama_produk || '';
        document.getElementById('editHargaBeli').value    = p.harga_beli || '';
        document.getElementById('editHargaJual').value    = p.harga_jual || '';
        document.getElementById('editStok').value         = p.stok_sekarang || 0;
        document.getElementById('editMinStok').value      = p.min_stok || 10;
        document.getElementById('editSatuan').value       = p.satuan || '';

        // Set kategori dropdown
        const selectKat = document.getElementById('editSelectKategori');
        selectKat.value = p.id_kategori || '';
        switchKategoriModeEdit('existing');

        // Set form action
        document.getElementById('formEditProduk').action = `/produk/${p.id_produk}`;

        // Preview foto existing (kalau ada)
        const editPreview = document.getElementById('editFotoPreviewImg');
        const editIcon    = document.getElementById('editFotoIcon');
        const editText    = document.getElementById('editFotoText');
        const editHint    = document.getElementById('editFotoHint');

        // Reset input file
        document.getElementById('editFotoInput').value = '';

        if (p.foto_produk) {
            editPreview.src = `/storage/${p.foto_produk}`;
            editPreview.style.display = 'block';
            editIcon.style.display = 'none';
            editText.textContent = 'Foto saat ini';
            editHint.textContent = 'Klik untuk ganti foto (otomatis dikompres ke WebP)';
        } else {
            editPreview.src = '';
            editPreview.style.display = 'none';
            editIcon.style.display = 'block';
            editText.textContent = 'Klik atau Tarik Foto Baru ke Sini';
            editHint.textContent = 'Format: JPG, PNG, WEBP (Maks. 2MB)';
        }

        // Tampilkan modal
        document.getElementById('modalEditProduk').classList.add('active');
    }

    function closeEditModal() {
        document.getElementById('modalEditProduk').classList.remove('active');
    }

    function switchKategoriModeEdit(mode) {
        const existing = document.getElementById('editKategoriModeExisting');
        const newMode  = document.getElementById('editKategoriModeNew');
        const selectEl = document.getElementById('editSelectKategori');
        const inputEl  = document.getElementById('editInputKategoriBaru');

        if (mode === 'new') {
            existing.style.display = 'none';
            newMode.style.display = 'block';
            selectEl.value = '';
            inputEl.focus();
        } else {
            existing.style.display = 'block';
            newMode.style.display = 'none';
            inputEl.value = '';
        }
    }

    function previewFotoEdit(input) {
        const preview = document.getElementById('editFotoPreviewImg');
        const icon    = document.getElementById('editFotoIcon');
        const text    = document.getElementById('editFotoText');
        const hint    = document.getElementById('editFotoHint');
        const box     = document.getElementById('editFotoDropzone');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                icon.style.display = 'none';
                text.textContent = input.files[0].name;
                text.style.color = '#16a34a';
                hint.textContent = '✓ Foto baru siap diupload';
                hint.style.color = '#16a34a';
                box.style.borderColor = '#16a34a';
                box.style.backgroundColor = '#f0fdf4';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ═══════════════════════════════════════════
    // MODAL HAPUS PRODUK
    // ═══════════════════════════════════════════
    function openDeleteModal(btn) {
        const id   = btn.dataset.id;
        const nama = btn.dataset.nama;

        document.getElementById('deleteNamaProduk').textContent = nama;
        document.getElementById('formHapusProduk').action = `/produk/${id}`;

        document.getElementById('modalHapusProduk').classList.add('active');
    }

    function closeDeleteModal() {
        document.getElementById('modalHapusProduk').classList.remove('active');
    }

    // ═══════════════════════════════════════════
    // EVENT LISTENER TAMBAHAN
    // ═══════════════════════════════════════════

    // Tutup Edit modal dengan klik overlay / ESC
    document.getElementById('modalEditProduk').addEventListener('click', function (e) {
        if (e.target === this) closeEditModal();
    });

    document.getElementById('modalHapusProduk').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeEditModal();
            closeDeleteModal();
        }
    });

    // ═══════════════════════════════════════════
    // TOAST NOTIFICATION
    // ═══════════════════════════════════════════
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;

        const iconClass = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
        toast.innerHTML = `<i class="fa-solid ${iconClass}"></i><span>${message}</span>`;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'toastOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3200);
    }

    // ═══════════════════════════════════════════
    // MODAL KELOLA KATEGORI
    // ═══════════════════════════════════════════
    let kategoriChanged = false;

    function openModalKategori() {
        kategoriChanged = false;
        document.getElementById('inputKategoriKelola').value = '';
        document.getElementById('kategoriTbody').innerHTML = `
            <tr>
                <td colspan="3" style="text-align: center; padding: 30px; color: #94a3b8;">
                    <i class="fa-solid fa-spinner fa-spin"></i> Memuat...
                </td>
            </tr>`;
        document.getElementById('modalKelolaKategori').classList.add('active');
        loadKategoriTable();
    }

    function closeModalKategori() {
        // Kalau ada perubahan, reload page biar pill filter & dropdown ikut sync
        if (kategoriChanged) {
            window.location.reload();
        } else {
            document.getElementById('modalKelolaKategori').classList.remove('active');
        }
    }

    async function loadKategoriTable() {
        try {
            const res = await fetch('{{ route("kategori.index") }}', {
                headers: { 'Accept': 'application/json' }
            });
            const list = await res.json();
            renderKategoriTable(list);
        } catch (err) {
            console.error(err);
            showToast('Gagal memuat daftar kategori.', 'error');
        }
    }

    function renderKategoriTable(list) {
        const tbody = document.getElementById('kategoriTbody');
        document.getElementById('kategoriCounter').textContent = list.length;

        if (list.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" style="text-align: center; padding: 30px; color: #94a3b8;">
                        Belum ada kategori. Tambahkan kategori pertama di atas.
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = list.map(kat => {
            const json = JSON.stringify(kat).replace(/"/g, '&quot;');
            return `
                <tr>
                    <td style="font-weight: 700;">${kat.nama_kategori}</td>
                    <td style="text-align: center;">
                        <span class="badge badge-info">${kat.jumlah_produk} produk</span>
                    </td>
                    <td>
                        <div class="action-btns-cell">
                            <button class="btn-icon" title="Rename Kategori"
                                    data-kat="${json}"
                                    onclick="openSubModalEditKategori(this)">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn-icon danger" title="Hapus Kategori"
                                    data-kat="${json}"
                                    onclick="hapusKategori(this)">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
        }).join('');
    }

    // ═══════════════════════════════════════════
    // TAMBAH KATEGORI (AJAX)
    // ═══════════════════════════════════════════
    async function submitKategoriTambah() {
        const input = document.getElementById('inputKategoriKelola');
        const btn = document.getElementById('btnTambahKategori');
        const nama = input.value.trim();

        if (!nama) {
            showToast('Nama kategori wajib diisi.', 'error');
            input.focus();
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        try {
            const res = await fetch('{{ route("kategori.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ nama_kategori: nama }),
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showToast(data.message, 'success');
                renderKategoriTable(data.kategori_list);
                input.value = '';
                input.focus();
                kategoriChanged = true;
            } else {
                const err = data.errors?.nama_kategori?.[0] || data.message || 'Gagal menyimpan kategori.';
                showToast(err, 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Terjadi kesalahan jaringan.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-plus"></i> Tambah';
        }
    }

    // ═══════════════════════════════════════════
    // EDIT KATEGORI (AJAX)
    // ═══════════════════════════════════════════
    function openSubModalEditKategori(btn) {
        const kat = JSON.parse(btn.dataset.kat);
        document.getElementById('editKategoriId').value = kat.id_kategori;
        document.getElementById('editKategoriNama').value = kat.nama_kategori;
        document.getElementById('subModalEditKategori').classList.add('active');
        setTimeout(() => document.getElementById('editKategoriNama').focus(), 100);
    }

    function closeSubModalEditKategori() {
        document.getElementById('subModalEditKategori').classList.remove('active');
    }

    async function submitKategoriEdit() {
        const id = document.getElementById('editKategoriId').value;
        const nama = document.getElementById('editKategoriNama').value.trim();
        const btn = document.getElementById('btnSimpanKategori');

        if (!nama) {
            showToast('Nama kategori wajib diisi.', 'error');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        try {
            const res = await fetch(`/kategori/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ nama_kategori: nama }),
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showToast(data.message, 'success');
                renderKategoriTable(data.kategori_list);
                closeSubModalEditKategori();
                kategoriChanged = true;
            } else {
                const err = data.errors?.nama_kategori?.[0] || data.message || 'Gagal menyimpan.';
                showToast(err, 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Terjadi kesalahan jaringan.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Simpan';
        }
    }

    // ═══════════════════════════════════════════
    // HAPUS KATEGORI (AJAX)
    // ═══════════════════════════════════════════
    async function hapusKategori(btn) {
        const kat = JSON.parse(btn.dataset.kat);

        if (kat.jumlah_produk > 0) {
            showToast(`Kategori "${kat.nama_kategori}" masih dipakai oleh ${kat.jumlah_produk} produk.`, 'error');
            return;
        }

        if (!confirm(`Yakin ingin menghapus kategori "${kat.nama_kategori}"?`)) {
            return;
        }

        try {
            const res = await fetch(`/kategori/${kat.id_kategori}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showToast(data.message, 'success');
                renderKategoriTable(data.kategori_list);
                kategoriChanged = true;
            } else {
                showToast(data.message || 'Gagal menghapus kategori.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Terjadi kesalahan jaringan.', 'error');
        }
    }

    // ═══════════════════════════════════════════
    // EVENT LISTENER TAMBAHAN (sub-modal edit)
    // ═══════════════════════════════════════════
    document.getElementById('subModalEditKategori').addEventListener('click', function (e) {
        if (e.target === this) closeSubModalEditKategori();
    });

    // Update ESC handler untuk handle sub-modal juga
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (document.getElementById('subModalEditKategori').classList.contains('active')) {
                closeSubModalEditKategori();
            } else if (document.getElementById('modalKelolaKategori').classList.contains('active')) {
                closeModalKategori();
            }
        }
    });


    // ═══════════════════════════════════════════
    // MODAL PENYESUAIAN STOK — SPLIT VIEW
    // ═══════════════════════════════════════════
    const opnameProducts = @json($opnameProductsData);

    let opnameActiveKategori = 'Semua';
    let opnameSelectedId = null;

    function openModalPenyesuaian() {
        // Reset state
        opnameActiveKategori = 'Semua';
        opnameSelectedId = null;

        document.getElementById('opnameSearch').value = '';
        document.getElementById('opnameStokFisik').value = '';
        document.getElementById('opnameSelectedProdukId').value = '';

        // Reset kategori pills
        document.querySelectorAll('#opnameCategories .opname-cat').forEach((el, i) => {
            el.classList.toggle('active', i === 0);
        });

        // Reset panel kanan
        document.getElementById('opnameEmptyState').style.display = 'flex';
        document.getElementById('opnameDetailPanel').style.display = 'none';
        document.getElementById('opnameSubmitBtn').disabled = true;

        renderOpnameList();
        document.getElementById('modalPenyesuaianStok').classList.add('active');
    }

    function closeModalPenyesuaian() {
        document.getElementById('modalPenyesuaianStok').classList.remove('active');
    }

    function setOpnameKategori(kategori, el) {
        opnameActiveKategori = kategori;
        document.querySelectorAll('#opnameCategories .opname-cat').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        renderOpnameList();
    }

    function filterOpnameProducts() {
        renderOpnameList();
    }

    function renderOpnameList() {
        const keyword = document.getElementById('opnameSearch').value.toLowerCase().trim();
        const list = document.getElementById('opnameList');

        const filtered = opnameProducts.filter(p => {
            const matchKat = opnameActiveKategori === 'Semua' || p.kategori === opnameActiveKategori;
            const haystack = `${p.nama} ${p.barcode || ''}`.toLowerCase();
            const matchSearch = keyword === '' || haystack.includes(keyword);
            return matchKat && matchSearch;
        });

        if (filtered.length === 0) {
            list.innerHTML = `
                <div class="opname-list-empty">
                    <i class="fa-solid fa-box-open"></i>
                    Produk tidak ditemukan
                </div>`;
            return;
        }

        list.innerHTML = filtered.map(p => `
            <div class="opname-item ${opnameSelectedId === p.id ? 'selected' : ''}"
                onclick="selectOpnameProduct(${p.id})">
                <div style="min-width: 0; flex: 1;">
                    <div class="opname-item-nama">${p.nama}</div>
                    <div class="opname-item-meta">${p.barcode || '-'} • ${p.kategori}</div>
                </div>
                <div class="opname-item-stok">${p.stok} ${p.satuan || ''}</div>
            </div>
        `).join('');
    }

    function selectOpnameProduct(id) {
        const p = opnameProducts.find(x => x.id === id);
        if (!p) return;

        opnameSelectedId = id;

        // Update hidden input
        document.getElementById('opnameSelectedProdukId').value = id;

        // Highlight di list
        document.querySelectorAll('#opnameList .opname-item').forEach(el => el.classList.remove('selected'));
        const selectedEl = [...document.querySelectorAll('#opnameList .opname-item')]
            .find(el => el.getAttribute('onclick')?.includes(`(${id})`));
        if (selectedEl) selectedEl.classList.add('selected');

        // Tampilkan panel detail
        document.getElementById('opnameEmptyState').style.display = 'none';
        document.getElementById('opnameDetailPanel').style.display = 'block';

        document.getElementById('opnameDetailNama').textContent = p.nama;
        document.getElementById('opnameDetailMeta').textContent = `${p.barcode || '-'} • ${p.kategori}`;
        document.getElementById('opnameStokSistem').textContent = `${p.stok} ${p.satuan || ''}`;

        // Reset input fisik + selisih
        document.getElementById('opnameStokFisik').value = '';
        const selisihEl = document.getElementById('opnameSelisih');
        selisihEl.textContent = '—';
        selisihEl.className = 'value neutral';

        document.getElementById('opnameSubmitBtn').disabled = true;

        // Fokus ke input
        setTimeout(() => document.getElementById('opnameStokFisik').focus(), 100);
    }

    function hitungSelisih() {
        const selisihEl = document.getElementById('opnameSelisih');
        const submitBtn = document.getElementById('opnameSubmitBtn');

        if (!opnameSelectedId) {
            selisihEl.textContent = '—';
            selisihEl.className = 'value neutral';
            submitBtn.disabled = true;
            return;
        }

        const p = opnameProducts.find(x => x.id === opnameSelectedId);
        const inputVal = document.getElementById('opnameStokFisik').value;

        if (inputVal === '') {
            selisihEl.textContent = '—';
            selisihEl.className = 'value neutral';
            submitBtn.disabled = true;
            return;
        }

        const stokFisik = parseInt(inputVal) || 0;
        const selisih = stokFisik - p.stok;
        const satuan = p.satuan || '';

        if (selisih === 0) {
            selisihEl.textContent = `0 ${satuan} — Tidak ada perubahan`;
            selisihEl.className = 'value neutral';
            submitBtn.disabled = true;
        } else if (selisih > 0) {
            selisihEl.textContent = `+${selisih} ${satuan}`;
            selisihEl.className = 'value positive';
            submitBtn.disabled = false;
        } else {
            selisihEl.textContent = `${selisih} ${satuan}`;
            selisihEl.className = 'value negative';
            submitBtn.disabled = false;
        }
    }

    // Tutup modal via overlay & ESC
    document.getElementById('modalPenyesuaianStok').addEventListener('click', function (e) {
        if (e.target === this) closeModalPenyesuaian();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && document.getElementById('modalPenyesuaianStok').classList.contains('active')) {
            closeModalPenyesuaian();
        }
    });

    // Auto-open kalau ada error validasi
    @if ($errors->hasAny(['id_produk', 'stok_fisik']))
        document.addEventListener('DOMContentLoaded', function () {
            openModalPenyesuaian();
        });
    @endif
</script>
@endpush