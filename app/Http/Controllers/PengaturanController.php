<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Pembelian;
use App\Models\Pengaturan;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Process;

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
        $validator = Validator::make($request->all(), [
            'nama_toko'   => ['required', 'string', 'max:255'],
            'alamat'      => ['required', 'string', 'max:255'],
            'no_telepon'  => ['required', 'string', 'max:50'],
            'logo'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'updateToko')->withInput()->with('tab', 'toko');
        }

        // Ambil data pengaturan pertama (ID 1), jika belum ada buat baru
        $toko = Pengaturan::firstOrCreate(['id' => 1]);

        $data = $request->only(['nama_toko', 'alamat', 'no_telepon']);

        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($toko->logo && Storage::disk('public')->exists($toko->logo)) {
                Storage::disk('public')->delete($toko->logo);
            }
            
            // Simpan logo baru ke folder storage/app/public/toko
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
        $validator = Validator::make($request->all(), [
            'nama_karyawan' => ['required', 'string', 'max:255'],
            'username'      => ['required', 'string', 'max:255', 'unique:karyawan,username'],
            'password'      => ['required', 'string', 'min:6'],
            'peran'         => ['required', 'in:admin,kasir'],
        ], [
            'username.unique' => 'Username sudah digunakan.',
            'password.min'    => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'tambahKaryawan')->withInput()->with('tab', 'karyawan');
        }

        Karyawan::create([
            'nama_karyawan' => $request->nama_karyawan,
            'username'      => $request->username,
            'password_hash' => Hash::make($request->password),
            'peran'         => $request->peran,
        ]);

        return back()->with('success', 'Karyawan baru berhasil ditambahkan!')->with('tab', 'karyawan');
    }

    public function hapusKaryawan($id)
    {
        $karyawan = Karyawan::findOrFail($id);

        // Proteksi: Tidak bisa menghapus diri sendiri
        if ($karyawan->id_karyawan === Auth::user()->id_karyawan) {
            return back()->with('error', 'Anda tidak bisa menghapus akun Anda sendiri!')->with('tab', 'karyawan');
        }

        // Proteksi: Minimal harus ada 1 admin yang tersisa
        if ($karyawan->peran === 'admin') {
            $jumlahAdmin = Karyawan::where('peran', 'admin')->count();
            if ($jumlahAdmin <= 1) {
                return back()->with('error', 'Tidak bisa menghapus satu-satunya akun Admin!')->with('tab', 'karyawan');
            }
        }

        // Proteksi FK RESTRICT di database (penjualan, pembelian, pengeluaran)
        if (
            Penjualan::where('id_karyawan', $id)->exists() ||
            Pembelian::where('id_karyawan', $id)->exists() ||
            Pengeluaran::where('id_karyawan', $id)->exists()
        ) {
            return back()
                ->with('error', "Akun \"{$karyawan->nama_karyawan}\" tidak bisa dihapus karena masih tercatat pada riwayat transaksi penjualan, pasokan, atau pengeluaran.")
                ->with('tab', 'karyawan');
        }

        $karyawan->delete();
        return back()->with('success', 'Akun karyawan berhasil dihapus.')->with('tab', 'karyawan');
    }

    // Fitur 4: Backup Database
    public function backupDatabase()
    {
        $dbName = (string) config('database.connections.mysql.database');
        $dbUser = (string) config('database.connections.mysql.username');
        $dbPass = (string) config('database.connections.mysql.password', '');
        $dbHost = (string) config('database.connections.mysql.host', '127.0.0.1');
        $dbPort = (string) config('database.connections.mysql.port', '3306');

        $mysqldumpBin = $this->findMysqldumpBinary();
        if (! $mysqldumpBin) {
            return back()->with('error', 'Binary mysqldump tidak ditemukan di server. Pastikan MySQL/Laragon bin tersedia.')->with('tab', 'backup');
        }

        $fileName = 'backup_laris_' . date('Y-m-d_H-i-s') . '.sql';
        $folderPath = storage_path('app/backups');
        $filePath = $folderPath . DIRECTORY_SEPARATOR . $fileName;

        if (! file_exists($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        $args = [
            $mysqldumpBin,
            '--routines',
            '--triggers',
            '--single-transaction',
            "--host={$dbHost}",
            "--port={$dbPort}",
            "--user={$dbUser}",
            $dbName,
        ];

        $env = $dbPass !== '' ? ['MYSQL_PWD' => $dbPass] : [];
        $process = new Process($args, null, $env, null, 120);

        $handle = fopen($filePath, 'wb');
        if (! $handle) {
            return back()->with('error', 'Gagal membuat file sementara backup.')->with('tab', 'backup');
        }

        try {
            $process->run(function ($type, $buffer) use ($handle) {
                if ($type === Process::OUT) {
                    fwrite($handle, $buffer);
                }
            });
        } finally {
            fclose($handle);
        }

        if ($process->isSuccessful() && file_exists($filePath) && filesize($filePath) > 0) {
            return response()->download($filePath)->deleteFileAfterSend(true);
        }

        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $errDetail = trim($process->getErrorOutput());
        return back()
            ->with('error', 'Gagal membuat backup database' . ($errDetail ? ': ' . $errDetail : '.'))
            ->with('tab', 'backup');
    }

    /**
     * Cari path binary mysqldump di PATH sistem maupun direktori Laragon Windows.
     */
    private function findMysqldumpBinary(): ?string
    {
        $candidates = [];

        if (PHP_OS_FAMILY === 'Windows') {
            $whereOut = @shell_exec('where mysqldump 2>NUL');
            if ($whereOut) {
                foreach (preg_split('/\r?\n/', trim($whereOut)) as $line) {
                    if ($line !== '') {
                        $candidates[] = trim($line);
                    }
                }
            }
            $laragonBins = glob('C:\\laragon\\bin\\mysql\\*\\bin\\mysqldump.exe') ?: [];
            $candidates = array_merge($candidates, $laragonBins);
        } else {
            $whichOut = @shell_exec('command -v mysqldump 2>/dev/null');
            if ($whichOut) {
                $candidates[] = trim($whichOut);
            }
        }

        foreach ($candidates as $path) {
            if ($path !== '' && is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}