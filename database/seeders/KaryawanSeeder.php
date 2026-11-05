<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KaryawanSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────
        // Akun Admin — Ina
        // ─────────────────────────────────────────────
        Karyawan::updateOrCreate(
            ['username' => 'ina'],           // kunci pencarian (kalau ada → update, kalau tidak → create)
            [
                'nama_karyawan' => 'Ina',
                'password_hash' => Hash::make(env('ADMIN_PASSWORD', 'password_rahasia')),
                'peran'         => 'admin',
            ]
        );

        // ─────────────────────────────────────────────
        // Akun Kasir — Kaka
        // ─────────────────────────────────────────────
        Karyawan::updateOrCreate(
            ['username' => 'kaka'],
            [
                'nama_karyawan' => 'Kaka',
                'password_hash' => Hash::make(env('KASIR_PASSWORD', 'password_rahasia')),
                'peran'         => 'kasir',
            ]
        );

        // Info di terminal setelah seeder selesai
        $this->command->newLine();
        $this->command->info('✅ Akun default berhasil dibuat:');
        $this->command->line('   • Admin → username: ina   | password: ' . env('ADMIN_PASSWORD', 'password_rahasia'));
        $this->command->line('   • Kasir → username: kaka  | password: ' . env('KASIR_PASSWORD', 'password_rahasia'));
        $this->command->newLine();
        $this->command->warn('⚠️  Ganti password setelah login pertama kali!');
        $this->command->newLine();
    }
}