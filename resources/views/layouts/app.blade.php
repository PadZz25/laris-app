<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - LARIS Toko Ina</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/laris.css') }}?v=12">
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
         GLOBAL CONFIRM MODAL (Pengganti confirm bawaan)
         ═══════════════════════════════════════════ -->
    <div class="modal-overlay" id="globalConfirmModal" style="z-index: 99999;">
        <div class="modal-card modal-card-sm">
            <div class="modal-header">
                <h3 id="globalConfirmTitle" style="color: #dc2626;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Konfirmasi
                </h3>
                <button type="button" class="btn-close-modal" onclick="closeGlobalConfirm()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <p id="globalConfirmMessage" style="font-size: 13px; color: #475569; line-height: 1.5;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeGlobalConfirm()">Batal</button>
                <button type="button" class="btn-danger" id="globalConfirmBtn">Ya, Lanjutkan</button>
            </div>
        </div>
    </div>

    @stack('scripts')

    <!-- ═══════════════════════════════════════════
         JAVASCRIPT GLOBAL
         ═══════════════════════════════════════════ -->
    <script>
        // --- 1. GLOBAL LOADING SPINNER ---
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
                form.addEventListener('submit', function() {
                    if (form.checkValidity()) {
                        showLoader();
                    }
                });
            });
        });

        // --- 2. GLOBAL CONFIRM LOGIC ---
        let globalConfirmCallback = null;

        function showGlobalConfirm(title, message, callback) {
            document.getElementById('globalConfirmTitle').innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="color: #dc2626;"></i> ${title}`;
            document.getElementById('globalConfirmMessage').innerText = message;
            globalConfirmCallback = callback;
            
            // Set aksi tombol Ya
            document.getElementById('globalConfirmBtn').onclick = function() {
                if (globalConfirmCallback) globalConfirmCallback();
                closeGlobalConfirm();
            };
            
            document.getElementById('globalConfirmModal').classList.add('active');
        }

        function closeGlobalConfirm() {
            document.getElementById('globalConfirmModal').classList.remove('active');
            globalConfirmCallback = null;
        }
    </script>
</body>
</html>