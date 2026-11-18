@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')

@section('content')

<div class="settings-layout">
    {{-- Menu Kiri --}}
    <div class="settings-menu">
        <div class="settings-menu-item active" id="menu-profil" onclick="switchTab('profil')">
            <i class="fa-solid fa-user"></i> Profil Akun
        </div>
        <div class="settings-menu-item" id="menu-keamanan" onclick="switchTab('keamanan')">
            <i class="fa-solid fa-shield-halved"></i> Keamanan Password
        </div>
        
        @if(auth()->user()->peran === 'admin')
            <hr class="settings-divider">
            <div class="settings-menu-item" id="menu-toko" onclick="switchTab('toko')">
                <i class="fa-solid fa-store"></i> Informasi Toko
            </div>
            <div class="settings-menu-item" id="menu-karyawan" onclick="switchTab('karyawan')">
                <i class="fa-solid fa-users"></i> Manajemen Karyawan
            </div>
            <div class="settings-menu-item" id="menu-backup" onclick="switchTab('backup')">
                <i class="fa-solid fa-database"></i> Backup & Sistem
            </div>
        @endif
    </div>

    {{-- Konten Kanan --}}
    <div class="settings-content">
        
        {{-- Tab 1: Profil --}}
        <div id="tab-profil" class="settings-pane active">
            <h2 class="settings-title">Profil Akun</h2>
            
            <form method="POST" action="{{ route('pengaturan.updateProfil') }}" class="settings-form">
                @csrf
                <div class="form-group">
                    <label for="profil-nama-karyawan">Nama Lengkap</label>
                    <input type="text" id="profil-nama-karyawan" name="nama_karyawan" autocomplete="name" value="{{ session('tab') === 'karyawan' ? auth()->user()->nama_karyawan : old('nama_karyawan', auth()->user()->nama_karyawan) }}" required>
                    @error('nama_karyawan') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="profil-username">Username (Untuk Login)</label>
                    <input type="text" id="profil-username" name="username" autocomplete="username" autocapitalize="none" autocorrect="off" spellcheck="false" value="{{ session('tab') === 'karyawan' ? auth()->user()->username : old('username', auth()->user()->username) }}" required>
                    @error('username') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                
                <button type="submit" class="btn-save settings-submit-btn">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Profil
                </button>
            </form>
        </div>

        {{-- Tab 2: Keamanan --}}
        <div id="tab-keamanan" class="settings-pane">
            <h2 class="settings-title">Ganti Password</h2>
            
            <form method="POST" action="{{ route('pengaturan.updatePassword') }}" class="settings-form">
                @csrf
                <div class="form-group">
                    <label for="current_password">Password Lama <span class="required">*</span></label>
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                    @error('current_password') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="new_password">Password Baru <span class="required">*</span></label>
                    <input type="password" id="new_password" name="new_password" autocomplete="new-password" required minlength="6">
                    @error('new_password') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="new_password_confirmation">Ulangi Password Baru <span class="required">*</span></label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" autocomplete="new-password" required minlength="6">
                </div>
                
                <button type="submit" class="btn-save settings-submit-btn">
                    <i class="fa-solid fa-key"></i> Perbarui Password
                </button>
            </form>
        </div>

        @if(auth()->user()->peran === 'admin')
            {{-- Tab 3: Informasi Toko --}}
            <div id="tab-toko" class="settings-pane">
                <h2 class="settings-title">Informasi Toko</h2>
                
                @php
                    $toko = \App\Models\Pengaturan::current();
                @endphp

                <form method="POST" action="{{ route('pengaturan.updateToko') }}" enctype="multipart/form-data" class="settings-form">
                    @csrf
                    <div class="form-group">
                        <label>Nama Toko <span class="required">*</span></label>
                        <input type="text" name="nama_toko" value="{{ old('nama_toko', $toko->nama_toko ?? 'TOKO INA') }}" required>
                        @error('nama_toko', 'updateToko') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Alamat Toko <span class="required">*</span></label>
                        <input type="text" name="alamat" value="{{ old('alamat', $toko->alamat ?? 'Desa Wates, Kec. Wates, Blitar') }}" required>
                        @error('alamat', 'updateToko') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>No. Telepon / WA <span class="required">*</span></label>
                        <input type="text" name="no_telepon" value="{{ old('no_telepon', $toko->no_telepon ?? '0812-3456-7890') }}" required>
                        @error('no_telepon', 'updateToko') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="form-group">
                        <label>Logo Toko (Untuk Struk)</label>
                        @if($toko && $toko->logo)
                            <div class="settings-logo-preview">
                                <img src="{{ asset('storage/' . $toko->logo) }}" alt="Logo Toko">
                            </div>
                        @endif
                        <input type="file" name="logo" accept="image/*" class="settings-file-input">
                        <div class="settings-help-text">Biarkan kosong jika tidak ingin mengubah logo.</div>
                        @error('logo', 'updateToko') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    
                    <button type="submit" class="btn-save settings-submit-btn">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Informasi Toko
                    </button>
                </form>
            </div>

            {{-- Tab 4: Manajemen Karyawan --}}
            <div id="tab-karyawan" class="settings-pane">
                <div class="settings-header-row">
                    <h2 class="settings-title">Manajemen Karyawan</h2>
                    <button onclick="openModalKaryawan()" class="btn-primary settings-btn-sm">
                        <i class="fa-solid fa-plus"></i> Tambah Karyawan
                    </button>
                </div>

                <div class="table-responsive settings-table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Peran</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Karyawan::orderBy('nama_karyawan')->get() as $kar)
                            <tr>
                                <td style="font-weight: 800;">{{ $kar->nama_karyawan }}</td>
                                <td>{{ $kar->username }}</td>
                                <td>
                                    @if($kar->peran === 'admin')
                                        <span class="badge badge-info">Admin</span>
                                    @else
                                        <span class="badge badge-warning">Kasir</span>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    @if($kar->id_karyawan !== auth()->user()->id_karyawan)
                                        <form method="POST" action="{{ route('pengaturan.hapusKaryawan', $kar->id_karyawan) }}" class="settings-inline-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn-icon danger" title="Hapus"
                                                    data-nama="{{ $kar->nama_karyawan }}"
                                                    onclick="confirmHapusKaryawan(this.form, this.dataset.nama)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="settings-self-label">(Akun Anda)</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tab 5: Backup & Sistem --}}
            <div id="tab-backup" class="settings-pane">
                <h2 class="settings-title">Backup & Sistem</h2>
                
                {{-- Card Backup --}}
                <div class="settings-card highlight">
                    <h3 class="settings-card-title"><i class="fa-solid fa-download"></i> Unduh Backup Database</h3>
                    <p class="settings-card-desc">
                        Sangat disarankan untuk mengunduh backup database secara rutin (misal: setiap akhir bulan). File ini berisi seluruh data produk, transaksi, kasbon, dan pengaturan toko Anda.
                    </p>
                    <a href="{{ route('pengaturan.backup') }}" class="btn-primary btn-backup-download">
                        <i class="fa-solid fa-file-export"></i> Unduh File .SQL Sekarang
                    </a>
                </div>

                {{-- Card Info Aplikasi --}}
                <div class="settings-card">
                    <h3 class="settings-card-title"><i class="fa-solid fa-circle-info"></i> Informasi Aplikasi</h3>
                    <table class="settings-info-table">
                        <tr>
                            <td>Nama Aplikasi</td>
                            <td>: LARIS (Layanan Aplikasi Ritel & Inventaris Sistem)</td>
                        </tr>
                        <tr>
                            <td>Versi</td>
                            <td>: 1.0.0 (Stable)</td>
                        </tr>
                        <tr>
                            <td>Framework</td>
                            <td>: Laravel 12 & MySQL 8</td>
                        </tr>
                        <tr>
                            <td>Pengembang</td>
                            <td>: Tim Pengembang LARIS - Universitas Negeri Malang</td>
                        </tr>
                        <tr>
                            <td>Hak Cipta</td>
                            <td>: &copy; {{ date('Y') }} {{ $toko->nama_toko ?? 'Toko Ina' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>

{{-- MODAL TAMBAH KARYAWAN --}}
@if(auth()->user()->peran === 'admin')
<div class="modal-overlay" id="modalTambahKaryawan">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-plus"></i> Tambah Karyawan Baru</h3>
            <button type="button" class="btn-close-modal" onclick="closeModalKaryawan()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="{{ route('pengaturan.tambahKaryawan') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="tambah-nama-karyawan">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" id="tambah-nama-karyawan" name="nama_karyawan" value="{{ session('tab') === 'karyawan' ? old('nama_karyawan') : '' }}" required>
                    @error('nama_karyawan', 'tambahKaryawan') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="tambah-username">Username (Untuk Login) <span class="required">*</span></label>
                    <input type="text" id="tambah-username" name="username" autocapitalize="none" autocorrect="off" spellcheck="false" value="{{ session('tab') === 'karyawan' ? old('username') : '' }}" required>
                    @error('username', 'tambahKaryawan') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="tambah-password">Password <span class="required">*</span></label>
                    <input type="password" id="tambah-password" name="password" autocomplete="new-password" minlength="6" required>
                    @error('password', 'tambahKaryawan') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label>Peran / Hak Akses <span class="required">*</span></label>
                    <select name="peran" required>
                        <option value="kasir" {{ session('tab') === 'karyawan' && old('peran') === 'kasir' ? 'selected' : '' }}>Kasir (Akses Kasir & Kasbon)</option>
                        <option value="admin" {{ session('tab') === 'karyawan' && old('peran') === 'admin' ? 'selected' : '' }}>Admin (Akses Penuh)</option>
                    </select>
                    @error('peran', 'tambahKaryawan') <div class="field-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModalKaryawan()">Batal</button>
                <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk"></i> Simpan Karyawan</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    function switchTab(tab) {
        ['profil', 'keamanan', 'toko', 'karyawan', 'backup'].forEach(name => {
            const tabEl = document.getElementById('tab-' + name);
            if (tabEl) tabEl.classList.remove('active');
            const menuEl = document.getElementById('menu-' + name);
            if (menuEl) menuEl.classList.remove('active');
        });

        const targetTab = document.getElementById('tab-' + tab);
        if (targetTab) {
            targetTab.classList.add('active');
            const selectedMenu = document.getElementById('menu-' + tab);
            if (selectedMenu) selectedMenu.classList.add('active');
        }
    }

    function openModalKaryawan() {
        const modal = document.getElementById('modalTambahKaryawan');
        if (modal) modal.classList.add('active');
    }

    function closeModalKaryawan() {
        const modal = document.getElementById('modalTambahKaryawan');
        if (modal) modal.classList.remove('active');
    }

    function confirmHapusKaryawan(form, nama) {
        showGlobalConfirm(
            'Hapus Akun Karyawan?',
            `Yakin ingin menghapus akun "${nama}"? Akun yang sudah pernah menangani transaksi tidak dapat dihapus.`,
            () => {
                if (typeof showLoader === 'function') showLoader();
                form.submit();
            }
        );
    }

    // Cek session tab / error validasi
    @if(session('tab') === 'keamanan' || $errors->hasAny(['current_password', 'new_password']))
        switchTab('keamanan');
    @elseif(session('tab') === 'toko' || $errors->updateToko->any())
        switchTab('toko');
    @elseif(session('tab') === 'karyawan' || $errors->tambahKaryawan->any())
        switchTab('karyawan');
        @if($errors->tambahKaryawan->any())
            document.addEventListener('DOMContentLoaded', () => openModalKaryawan());
        @endif
    @elseif(session('tab') === 'backup')
        switchTab('backup');
    @endif
</script>
@endpush
