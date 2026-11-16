<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KaryawanSeeder extends Seeder
{
    public function run(): void
    {
        // Gunakan akun karyawan yang sudah ada di database
        if (Karyawan::exists()) {
            if ($this->command) {
                $jumlah = Karyawan::count();
                $this->command->info("  Database sudah memiliki {$jumlah} akun karyawan. Seeder dilewati agar data tidak tertimpa.");
            }
            return;
        }

        // Jika tabel karyawan masih kosong, ambil kredensial awal hanya dari .env lokal
        $adminUsername = trim((string) env('SEED_ADMIN_USERNAME', ''));
        $adminPassword = (string) env('SEED_ADMIN_PASSWORD', '');
        $adminName     = trim((string) env('SEED_ADMIN_NAME', 'Administrator')) ?: 'Administrator';

        if ($adminUsername === '' || $adminPassword === '') {
            if ($this->command) {
                $this->command->warn('Tabel karyawan kosong dan SEED_ADMIN_USERNAME / SEED_ADMIN_PASSWORD belum diisi di .env.');
                $this->command->line('Import database SQL atau isi variabel SEED_ADMIN_* di file .env lokal sebelum menjalankan seeder.');
            }
            return;
        }

        Karyawan::create([
            'nama_karyawan' => $adminName,
            'username'      => $adminUsername,
            'password_hash' => Hash::make($adminPassword),
            'peran'         => 'admin',
        ]);

        // Opsional: akun kasir awal hanya dibuat jika disetel pada .env lokal
        $kasirUsername = trim((string) env('SEED_KASIR_USERNAME', ''));
        $kasirPassword = (string) env('SEED_KASIR_PASSWORD', '');
        $kasirName     = trim((string) env('SEED_KASIR_NAME', 'Kasir')) ?: 'Kasir';

        if ($kasirUsername !== '' && $kasirPassword !== '' && $kasirUsername !== $adminUsername) {
            Karyawan::create([
                'nama_karyawan' => $kasirName,
                'username'      => $kasirUsername,
                'password_hash' => Hash::make($kasirPassword),
                'peran'         => 'kasir',
            ]);
        }

        if ($this->command) {
            $this->command->info(' Akun awal karyawan berhasil dibuat dari konfigurasi .env lokal.');
        }
    }
}
