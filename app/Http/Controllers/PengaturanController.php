<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
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

    // Fitur 2: Update Password
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

    public function updateToko(Request $request)
    {
        $request->validate([
            'nama_toko'   => ['required', 'string', 'max:255'],
            'alamat'      => ['required', 'string', 'max:255'],
            'no_telepon'  => ['required', 'string', 'max:50'],
            'logo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        // Ambil data pengaturan pertama (ID 1), jika belum ada buat baru
        $toko = Pengaturan::firstOrCreate(['id' => 1]);

        $data = $request->only(['nama_toko', 'alamat', 'no_telepon']);

        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($toko->logo && Storage::disk('public')->exists($toko->logo)) {
                Storage::disk('public')->delete($toko->logo);
            }
            
            // Simpan logo baru ke folder public/toko
            $file = $request->file('logo');
            $filename = 'toko/logo_' . time() . '.webp';
            $image = Image::read($file)->scale(width: 400)->toWebp(80);
            Storage::disk('public')->put($filename, (string) $image);
            
            $data['logo'] = $filename;
        }

        $toko->update($data);

        return back()->with('success', 'Informasi Toko berhasil diperbarui!')->with('tab', 'toko');
    }

        // Fitur 3: Manajemen Karyawan
    public function tambahKaryawan(Request $request)
    {
        $request->validate([
            'nama_karyawan' => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'max:255', 'unique:karyawan,username'],
            'password'      => ['required', 'string', 'min:6'],
            'peran'         => ['required', 'in:admin,kasir'],
        ], [
            'username.unique' => 'Username sudah digunakan.',
            'password.min'    => 'Password minimal 6 karakter.',
        ]);

        \App\Models\Karyawan::create([
            'nama_karyawan' => $request->nama_karyawan,
            'username'      => $request->username,
            'password_hash' => Hash::make($request->password),
            'peran'         => $request->peran,
        ]);

        return back()->with('success', 'Karyawan baru berhasil ditambahkan!')->with('tab', 'karyawan');
    }

    public function hapusKaryawan($id)
    {
        $karyawan = \App\Models\Karyawan::findOrFail($id);

        // Proteksi: Tidak bisa menghapus diri sendiri
        if ($karyawan->id_karyawan === Auth::user()->id_karyawan) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri!');
        }

        // Proteksi: Minimal harus ada 1 admin yang tersisa
        if ($karyawan->peran === 'admin') {
            $jumlahAdmin = \App\Models\Karyawan::where('peran', 'admin')->count();
            if ($jumlahAdmin <= 1) {
                return back()->with('error', 'Tidak bisa menghapus satu-satunya akun Admin!');
            }
        }

        $karyawan->delete();
        return back()->with('success', 'Akun karyawan berhasil dihapus.')->with('tab', 'karyawan');
    }

        // Fitur 4: Backup Database
    public function backupDatabase()
    {
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');
        $dbHost = env('DB_HOST', '127.0.0.1');
        
        $fileName = 'backup_laris_' . date('Y-m-d_H-i-s') . '.sql';
        $folderPath = storage_path('app/public/backups');
        $filePath = $folderPath . '/' . $fileName;

        // Buat folder jika belum ada
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        // Perintah mysqldump (Standar Windows/Laragon)
        $command = "mysqldump --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > \"{$filePath}\"";
        
        exec($command, $output, $returnVar);

        if ($returnVar === 0 && file_exists($filePath)) {
            // Hapus file setelah diunduh agar tidak menumpuk di server
            return response()->download($filePath)->deleteFileAfterSend(true);
        }
        
        return back()->with('error', 'Gagal membuat backup. Pastikan Anda menggunakan Laragon dan MySQL berjalan.');
    }
}