<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title>Login - LARIS Toko Ina</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; scrollbar-width: none; -ms-overflow-style: none; }
        *::-webkit-scrollbar { width: 0; height: 0; display: none; }

        body {
            background-color: #cbd5e1;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 20px;
        }

        .auth-container {
            width: 860px; max-width: 100%;
            background-color: #ffffff; border-radius: 28px;
            display: flex; overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
        }

        .auth-banner {
            flex: 1.1;
            background: linear-gradient(180deg, #162F32 0%, #429198 100%);
            padding: 48px 40px; color: #ffffff;
            display: flex; flex-direction: column; justify-content: space-between;
        }

        .logo-wrapper { display: flex; align-items: center; gap: 14px; margin-bottom: 36px; }

        .logo-badge {
            width: 48px; height: 48px; border-radius: 14px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex; align-items: center; justify-content: center;
            color: #ffffff; font-size: 22px;
        }

        .logo-text-group { display: flex; flex-direction: column; }

        .brand-title { font-size: 26px; font-weight: 900; letter-spacing: 0.5px; line-height: 1; color: #ffffff; }
        .brand-title span { color: #38bdf8; }
        .brand-tagline { font-size: 9px; font-weight: 700; color: #cbd5e1; letter-spacing: 0.5px; margin-top: 4px; text-transform: uppercase; }

        .banner-content h2 { font-size: 22px; font-weight: 800; line-height: 1.35; letter-spacing: -0.3px; }
        .banner-content p { font-size: 12.5px; color: #e2e8f0; margin-top: 10px; line-height: 1.6; }

        .banner-footer-info {
            font-size: 10.5px; color: #cbd5e1;
            display: flex; align-items: center; gap: 8px;
            border-top: 1px solid rgba(255, 255, 255, 0.2); padding-top: 16px;
        }

        .auth-form-card { flex: 1; padding: 48px 40px; display: flex; flex-direction: column; justify-content: center; }

        .form-header h3 { font-size: 22px; font-weight: 800; color: #0f172a; }
        .form-header p { font-size: 12px; color: #64748b; margin-top: 4px; margin-bottom: 28px; }

        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
        .form-group label { font-size: 11px; font-weight: 700; color: #0f172a; }

        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper .input-icon { position: absolute; left: 14px; color: #64748b; font-size: 14px; pointer-events: none; }

        .btn-toggle-password {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            padding: 4px;
            cursor: pointer;
            color: #64748b;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-toggle-password:hover { color: #0f172a; }

        .input-wrapper input {
            width: 100%; padding: 12px 14px 12px 42px;
            border-radius: 10px; border: 1px solid #cbd5e1;
            outline: none; font-size: 12px; color: #0f172a;
            transition: all 0.2s;
        }
        .input-wrapper input.input-password-field { padding-right: 45px; }

        .input-wrapper input:focus {
            border-color: #429198;
            box-shadow: 0 0 0 3px rgba(66, 145, 152, 0.15);
        }

        .input-wrapper input.error { border-color: #ef4444; }

        .form-options {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 24px; font-size: 11px;
        }

        .remember-checkbox { display: flex; align-items: center; gap: 6px; color: #475569; cursor: pointer; font-weight: 600; }
        .forgot-link { color: #429198; text-decoration: none; font-weight: 700; }

        .btn-submit-login {
            width: 100%; padding: 13px; border-radius: 10px;
            background-color: #0f172a; color: #ffffff;
            font-size: 13px; font-weight: 800; border: none;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            gap: 8px; transition: background 0.2s;
        }
        .btn-submit-login:hover { background-color: #162F32; }

        .auth-footer-text { margin-top: 28px; text-align: center; font-size: 10px; color: #94a3b8; }

        /* Alert messages */
        .alert {
            padding: 10px 14px; border-radius: 10px;
            font-size: 11px; font-weight: 600;
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 18px;
        }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }

        @media (max-width: 768px) {
            .auth-container { flex-direction: column; }
            .auth-banner { padding: 32px 24px; }
        }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none !important;
        }
    </style>
</head>
<body>

    <div class="auth-container">

        {{-- SISI KIRI: BRANDING --}}
        <div class="auth-banner">
            <div>
                <div class="logo-wrapper">
                    <div class="logo-badge"><i class="fa-solid fa-store"></i></div>
                    <div class="logo-text-group">
                        <div class="brand-title">LARIS<span>.</span></div>
                        <div class="brand-tagline">Layanan AplikASI RITEL & INVENTARIS SISTEM</div>
                    </div>
                </div>

                <div class="banner-content">
                    <h2>Sistem Kelola Toko Ina</h2>
                    <p>Aplikasi pengelolaan transaksi kasir, inventaris barang, dan pencatatan kasbon Toko Ina Desa Wates.</p>
                </div>
            </div>

            <div class="banner-footer-info">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Toko Ina Desa Wates</span>
            </div>
        </div>

        {{-- SISI KANAN: FORM LOGIN --}}
        <div class="auth-form-card">
            <div class="form-header">
                <h3>Masuk Akun</h3>
                <p>Silakan login untuk mengakses dashboard LARIS.</p>
            </div>

            {{-- Alert Error (kalau login gagal) --}}
            @if ($errors->any())
                <div class="alert alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Alert Success (kalau baru logout) --}}
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm" autocomplete="on">
                @csrf

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fa-regular fa-user input-icon"></i>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            autocomplete="username"
                            autocapitalize="none"
                            spellcheck="false"
                            placeholder="Masukkan username..."
                            value="{{ old('username') }}"
                            class="@error('username') error @enderror"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="input-password">Kata Sandi</label>
                    <div class="input-wrapper">
                        {{-- Ikon Gembok --}}
                        <i class="fa-solid fa-lock input-icon"></i>
                        
                        {{-- Input Password --}}
                        <input
                            type="password"
                            name="password"
                            id="input-password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="input-password-field @error('password') error @enderror"
                            required
                        >
                        
                        {{-- Tombol Lihat Password --}}
                        <button type="button"
                                class="btn-toggle-password"
                                id="toggle-password-btn"
                                onclick="togglePassword()"
                                tabindex="-1"
                                aria-label="Tampilkan atau sembunyikan kata sandi">
                            <i class="fa-regular fa-eye" id="toggle-password-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-checkbox">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                        Ingat Sesi Saya
                    </label>
                    <a href="#" class="forgot-link" onclick="alert('Silakan hubungi Admin toko untuk reset kata sandi.'); return false;">Lupa Sandi?</a>
                </div>

                <button type="submit" class="btn-submit-login">
                    <i class="fa-solid fa-right-to-bracket"></i> Masuk ke LARIS
                </button>
            </form>

            <div class="auth-footer-text">
                &copy; 2026 <strong>LARIS</strong> • Toko Ina Desa Wates
            </div>
        </div>

    </div>
        <script>
        function togglePassword() {
            const input = document.getElementById('input-password');
            const icon = document.getElementById('toggle-password-icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Pastikan tipe field kembali menjadi password saat submit agar password manager mengenali kredensial
        document.getElementById('loginForm').addEventListener('submit', function () {
            document.getElementById('input-password').type = 'password';
        });
    </script>
</body>
</html>