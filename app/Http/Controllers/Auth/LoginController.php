<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman form login.
     */
    public function showLoginForm()
    {
        // Kalau sudah login, langsung lempar ke dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Proses login dari form.
     */
    public function login(Request $request)
    {
        // 1. Validasi input dasar
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // 2. Cek "Ingat Sesi Saya"
        $remember = $request->boolean('remember');

        // 3. Coba login
        if (Auth::attempt($credentials, $remember)) {
            // Regenerate session — proteksi session fixation
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang, ' . Auth::user()->nama_karyawan . '!');
        }

        // 4. Kalau gagal, kembalikan ke form dengan pesan error
        return back()
            ->withInput($request->only('username'))
            ->withErrors([
                'username' => 'Username atau kata sandi salah.',
            ]);
    }

    /**
     * Proses logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar.');
    }
}