<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - LARIS Toko Ina</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/laris.css') }}?v=10">
    @stack('styles')
</head>
<body>

    <!-- GLOBAL LOADING OVERLAY -->
    <div id="global-loader">
        <div class="loader-spinner"></div>
        <div class="loader-text">Memproses...</div>
    </div>

    <!-- TOAST CONTAINER -->
    <div class="toast-container" id="toastContainer"></div>

    @include('partials.sidebar')

    <div class="main-container">
        @include('partials.header', ['title' => trim($__env->yieldContent('page-title', 'Dashboard'))])

        @if (session('success'))
            <div class="flash-message flash-success">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="flash-message flash-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    <!-- ═══════════════════════════════════════════
         MODAL: GANTI PASSWORD (Global)
         ═══════════════════════════════════════════ -->
    <div class="modal-overlay" id="modalGantiPassword">
        <div class="modal-card modal-card-sm">
            <div class="modal-header">
                <h3><i class="fa-solid fa-key"></i> Ganti Password</h3>
                <button type="button" class="btn-close-modal" onclick="closeModalPassword()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Password Lama <span class="required">*</span></label>
                        <input type="password" name="current_password" placeholder="Masukkan password saat ini..." required>
                    </div>
                    <div class="form-group">
                        <label>Password Baru <span class="required">*</span></label>
                        <input type="password" name="new_password" placeholder="Minimal 6 karakter..." required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Ulangi Password Baru <span class="required">*</span></label>
                        <input type="password" name="new_password_confirmation" placeholder="Ketik ulang password baru..." required minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalPassword()">Batal</button>
                    <button type="submit" class="btn-save"><i class="fa-solid fa-floppy-disk"></i> Simpan Password</button>
                </div>
            </form>
        </div>
    </div>

    @stack('scripts')

    <!-- ═══════════════════════════════════════════
         JAVASCRIPT GLOBAL
         ═══════════════════════════════════════════ -->
    <script>
        // 1. Fungsi Loading Spinner
        function showLoader() {
            document.getElementById('global-loader').classList.add('active');
        }

        function hideLoader() {
            document.getElementById('global-loader').classList.remove('active');
        }

        // Otomatis tampilkan loader saat form disubmit
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Cek jika tombol submit ditekan (bukan sekadar validasi error HTML5)
                    if (form.checkValidity()) {
                        showLoader();
                    }
                });
            });
        });

        // 2. Fungsi Modal Ganti Password
        function openModalPassword() {
            document.getElementById('modalGantiPassword').classList.add('active');
        }

        function closeModalPassword() {
            document.getElementById('modalGantiPassword').classList.remove('active');
        }

        // 3. Fungsi Tutup Aplikasi (Khusus Desktop/WebView2)
        function tutupAplikasi() {
            if (confirm("Apakah Anda yakin ingin menutup aplikasi LARIS?")) {
                // Trik menutup window WebView2
                window.open('', '_self', '');
                window.close();
                
                // Fallback jika ditolak browser: lempar ke halaman logout
                setTimeout(() => {
                    window.location.href = "{{ route('logout') }}";
                }, 500);
            }
        }

        // Auto-open modal password jika ada error validasi password dari server
        @if ($errors->hasAny(['current_password', 'new_password']))
            document.addEventListener('DOMContentLoaded', function () {
                openModalPassword();
            });
        @endif
    </script>
</body>
</html>