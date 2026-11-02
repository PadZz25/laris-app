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
            <div class="settings-menu-item" onclick="alert('Fitur Karyawan segera hadir!')">
                <i class="fa-solid fa-users"></i> Manajemen Karyawan
            </div>
            <div class="settings-menu-item" onclick="alert('Fitur Backup segera hadir!')">
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

        {{-- Tab 3: Informasi Toko --}}
        @if(auth()->user()->peran === 'admin')
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
        @endif

    </div>
</div>

@endsection

@push('scripts')
<script>
    function switchTab(tab) {
        // Sembunyikan semua tab
        document.getElementById('tab-profil').style.display = 'none';
        document.getElementById('tab-keamanan').style.display = 'none';
        
        const tabToko = document.getElementById('tab-toko');
        if (tabToko) tabToko.style.display = 'none';
        
        // Hapus class active dari menu
        document.getElementById('menu-profil').classList.remove('active');
        document.getElementById('menu-keamanan').classList.remove('active');
        
        const menuToko = document.getElementById('menu-toko');
        if (menuToko) menuToko.classList.remove('active');

        // Tampilkan tab yang dipilih
        const selectedTab = document.getElementById('tab-' + tab);
        if (selectedTab) selectedTab.style.display = 'block';
        
        const selectedMenu = document.getElementById('menu-' + tab);
        if (selectedMenu) selectedMenu.classList.add('active');
    }

    // Cek jika ada session 'tab' atau error validasi untuk auto-buka tab yang tepat
    @if(session('tab') === 'keamanan' || $errors->hasAny(['current_password', 'new_password']))
        switchTab('keamanan');
    @elseif(session('tab') === 'toko')
        switchTab('toko');
    @endif
</script>
@endpush