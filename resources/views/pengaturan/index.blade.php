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
            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 8px 0;">
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
        <div id="tab-profil" style="display: block;">
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 20px;">Profil Akun</h2>
            
            <form method="POST" action="{{ route('pengaturan.updateProfil') }}" style="max-width: 400px;">
                @csrf
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_karyawan" value="{{ auth()->user()->nama_karyawan }}" required>
                    @error('nama_karyawan') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group" style="margin-top: 14px;">
                    <label>Username (Untuk Login)</label>
                    <input type="text" name="username" value="{{ auth()->user()->username }}" required>
                    @error('username') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                
                <button type="submit" class="btn-save" style="margin-top: 20px;">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Profil
                </button>
            </form>
        </div>

        {{-- Tab 2: Keamanan --}}
        <div id="tab-keamanan" style="display: none;">
            <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 20px;">Ganti Password</h2>
            
            <form method="POST" action="{{ route('pengaturan.updatePassword') }}" style="max-width: 400px;">
                @csrf
                <div class="form-group">
                    <label>Password Lama <span class="required">*</span></label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group" style="margin-top: 14px;">
                    <label>Password Baru <span class="required">*</span></label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <div class="form-group" style="margin-top: 14px;">
                    <label>Ulangi Password Baru <span class="required">*</span></label>
                    <input type="password" name="new_password_confirmation" required minlength="6">
                </div>
                
                <button type="submit" class="btn-save" style="margin-top: 20px;">
                    <i class="fa-solid fa-key"></i> Perbarui Password
                </button>
            </form>
        </div>

        @if(auth()->user()->peran === 'admin')
            {{-- Tab 3: Informasi Toko --}}
            <div id="tab-toko" style="display: none;">
                <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 20px;">Informasi Toko</h2>
                
                @php
                    $toko = \App\Models\Pengaturan::first();
                @endphp

                <form method="POST" action="{{ route('pengaturan.updateToko') }}" enctype="multipart/form-data" style="max-width: 400px;">
                    @csrf
                    <div class="form-group">
                        <label>Nama Toko <span class="required">*</span></label>
                        <input type="text" name="nama_toko" value="{{ $toko->nama_toko ?? 'TOKO INA' }}" required>
                    </div>
                    <div class="form-group" style="margin-top: 14px;">
                        <label>Alamat Toko <span class="required">*</span></label>
                        <input type="text" name="alamat" value="{{ $toko->alamat ?? 'Desa Wates, Kec. Wates, Blitar' }}" required>
                    </div>
                    <div class="form-group" style="margin-top: 14px;">
                        <label>No. Telepon / WA <span class="required">*</span></label>
                        <input type="text" name="no_telepon" value="{{ $toko->no_telepon ?? '0812-3456-7890' }}" required>
                    </div>
                    
                    <div class="form-group" style="margin-top: 14px;">
                        <label>Logo Toko (Untuk Struk)</label>
                        @if($toko && $toko->logo)
                            <div style="margin-bottom: 8px;">
                                <img src="{{ asset('storage/' . $toko->logo) }}" alt="Logo Toko" style="height: 60px; background: #fff; padding: 4px; border-radius: 8px; border: 1px solid #cbd5e1;">
                            </div>
                        @endif
                        <input type="file" name="logo" accept="image/*" style="padding: 8px; border: 1px dashed #cbd5e1; border-radius: 8px;">
                        <div style="font-size: 10px; color: #64748b; margin-top: 4px;">Biarkan kosong jika tidak ingin mengubah logo.</div>
                    </div>
                    
                    <button type="submit" class="btn-save" style="margin-top: 20px;">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Informasi Toko
                    </button>
                </form>
            </div>

            {{-- Tab 4: Manajemen Karyawan --}}
            <div id="tab-karyawan" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="font-size: 18px; font-weight: 800; color: #0f172a;">Manajemen Karyawan</h2>
                    <button onclick="openModalKaryawan()" class="btn-primary" style="padding: 8px 16px; font-size: 12px;">
                        <i class="fa-solid fa-plus"></i> Tambah Karyawan
                    </button>
                </div>

                <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <table class="data-table" style="font-size: 12px; margin: 0;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Peran</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Karyawan::all() as $kar)
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
                                        <form method="POST" action="{{ route('pengaturan.hapusKaryawan', $kar->id_karyawan) }}" style="display: inline-block;" onsubmit="return confirm('Yakin ingin menghapus akun ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon danger" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span style="font-size: 10px; color: #94a3b8;">(Akun Anda)</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

                    {{-- Tab 5: Backup & Sistem --}}
            <div id="tab-backup" style="display: none;">
                <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 20px;">Backup & Sistem</h2>
                
                {{-- Card Backup --}}
                <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 20px; background: #f8fafc;">
                    <h3 style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 8px;"><i class="fa-solid fa-download" style="color: #0284c7;"></i> Unduh Backup Database</h3>
                    <p style="font-size: 11px; color: #64748b; margin-bottom: 16px; line-height: 1.5;">
                        Sangat disarankan untuk mengunduh backup database secara rutin (misal: setiap akhir bulan). File ini berisi seluruh data produk, transaksi, kasbon, dan pengaturan toko Anda.
                    </p>
                    <a href="{{ route('pengaturan.backup') }}" class="btn-primary" style="text-decoration: none; display: inline-flex; background: #16a34a;">
                        <i class="fa-solid fa-file-export"></i> Unduh File .SQL Sekarang
                    </a>
                </div>

                {{-- Card Info Aplikasi --}}
                <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                    <h3 style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 8px;"><i class="fa-solid fa-circle-info" style="color: #0284c7;"></i> Informasi Aplikasi</h3>
                    <table style="font-size: 11px; color: #475569; width: 100%; max-width: 400px; line-height: 2;">
                        <tr>
                            <td style="font-weight: 700;">Nama Aplikasi</td>
                            <td>: LARIS (Layanan Aplikasi Ritel & Inventaris Sistem)</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700;">Versi</td>
                            <td>: 1.0.0 (Stable)</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700;">Framework</td>
                            <td>: Laravel 12 & MySQL 8</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700;">Pengembang</td>
                            <td>: Tim Pengembang LARIS - Universitas Negeri Malang</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 700;">Hak Cipta</td>
                            <td>: &copy; 2026 Toko Ina Desa Wates</td>
                        </tr>
                    </table>
                </div>
            </div>

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
                    <label>Nama Lengkap <span class="required">*</span></label>
                    <input type="text" name="nama_karyawan" required>
                </div>
                <div class="form-group">
                    <label>Username (Untuk Login) <span class="required">*</span></label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="password" minlength="6" required>
                </div>
                <div class="form-group">
                    <label>Peran / Hak Akses <span class="required">*</span></label>
                    <select name="peran" required>
                        <option value="kasir">Kasir (Akses Kasir & Kasbon)</option>
                        <option value="admin">Admin (Akses Penuh)</option>
                    </select>
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
        // Sembunyikan semua tab
        document.getElementById('tab-profil').style.display = 'none';
        document.getElementById('tab-keamanan').style.display = 'none';
        
        const tabToko = document.getElementById('tab-toko');
        if (tabToko) tabToko.style.display = 'none';

        const tabKaryawan = document.getElementById('tab-karyawan');
        if (tabKaryawan) tabKaryawan.style.display = 'none';
        
        // Hapus class active
        document.getElementById('menu-profil').classList.remove('active');
        document.getElementById('menu-keamanan').classList.remove('active');
        
        const menuToko = document.getElementById('menu-toko');
        if (menuToko) menuToko.classList.remove('active');

        const menuKaryawan = document.getElementById('menu-karyawan');
        if (menuKaryawan) menuKaryawan.classList.remove('active');

        // Tampilkan yang dipilih
        document.getElementById('tab-' + tab).style.display = 'block';
        const selectedMenu = document.getElementById('menu-' + tab);
        if (selectedMenu) selectedMenu.classList.add('active');
    }

    function openModalKaryawan() {
        document.getElementById('modalTambahKaryawan').classList.add('active');
    }

    function closeModalKaryawan() {
        document.getElementById('modalTambahKaryawan').classList.remove('active');
    }

        function switchTab(tab) {
        // Sembunyikan semua tab
        document.getElementById('tab-profil').style.display = 'none';
        document.getElementById('tab-keamanan').style.display = 'none';
        
        const tabToko = document.getElementById('tab-toko');
        if (tabToko) tabToko.style.display = 'none';

        const tabKaryawan = document.getElementById('tab-karyawan');
        if (tabKaryawan) tabKaryawan.style.display = 'none';

        const tabBackup = document.getElementById('tab-backup');
        if (tabBackup) tabBackup.style.display = 'none';
        
        // Hapus class active
        document.getElementById('menu-profil').classList.remove('active');
        document.getElementById('menu-keamanan').classList.remove('active');
        
        const menuToko = document.getElementById('menu-toko');
        if (menuToko) menuToko.classList.remove('active');

        const menuKaryawan = document.getElementById('menu-karyawan');
        if (menuKaryawan) menuKaryawan.classList.remove('active');

        const menuBackup = document.getElementById('menu-backup');
        if (menuBackup) menuBackup.classList.remove('active');

        // Tampilkan yang dipilih
        document.getElementById('tab-' + tab).style.display = 'block';
        const selectedMenu = document.getElementById('menu-' + tab);
        if (selectedMenu) selectedMenu.classList.add('active');
    }

    // Cek session tab
    @if(session('tab') === 'keamanan' || $errors->hasAny(['current_password', 'new_password']))
        switchTab('keamanan');
    @elseif(session('tab') === 'toko')
        switchTab('toko');
    @elseif(session('tab') === 'karyawan')
        switchTab('karyawan');
    @endif
</script>
@endpush