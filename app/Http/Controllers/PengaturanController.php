<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('pengaturan.index');
    }

    // Fitur 1: Update Profil (Nama & Username)
    public function updateProfil(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nama_karyawan' => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'max:255', 'unique:karyawan,username,' . $user->id_karyawan . ',id_karyawan'],
        ], [
            'nama_karyawan.required' => 'Nama wajib diisi.',
            'username.required'      => 'Username wajib diisi.',
            'username.unique'        => 'Username sudah dipakai orang lain.',
        ]);

        $user->update([
            'nama_karyawan' => $request->nama_karyawan,
            'username'      => $request->username,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    // Fitur 1: Update Password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password'     => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'new_password.required'     => 'Password baru wajib diisi.',
            'new_password.min'          => 'Password minimal 6 karakter.',
            'new_password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password_hash)) {
            return back()->with('error', 'Password lama yang Anda masukkan salah!')->with('tab', 'keamanan');
        }

        $user->update([
            'password_hash' => Hash::make($request->new_password)
        ]);

        return back()->with('success', 'Password berhasil diubah!')->with('tab', 'keamanan');
    }
}