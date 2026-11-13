<?php

namespace Tests\Feature;

use App\Models\DetailPenjualan;
use App\Models\Karyawan;
use App\Models\Pelanggan;
use App\Models\Pengaturan;
use App\Models\Penjualan;
use App\Models\Produk;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class LarisWorkflowTest extends TestCase
{
    private function makeUser(string $peran, int $id = 2, string $nama = 'Kasir Uji'): Karyawan
    {
        $user = new Karyawan([
            'nama_karyawan' => $nama,
            'username'      => $peran . '_uji',
            'peran'         => $peran,
        ]);
        $user->id_karyawan = $id;
        $user->exists = true;

        return $user;
    }

    public function test_app_timezone_reads_env_config(): void
    {
        $config = require base_path('config/app.php');
        $expected = env('APP_TIMEZONE', 'UTC');

        $this->assertSame($expected, $config['timezone']);
    }

    public function test_pengaturan_current_fails_safely_when_database_unavailable(): void
    {
        $this->assertNull(Pengaturan::current());
    }

    public function test_kasir_is_blocked_from_all_admin_routes(): void
    {
        $kasir = $this->makeUser('kasir', 2, 'Kasir Toko');

        $this->actingAs($kasir)->get('/laporan')->assertRedirect(route('kasir.index'));
        $this->actingAs($kasir)->get('/supplier')->assertRedirect(route('kasir.index'));
        $this->actingAs($kasir)->get('/pengeluaran')->assertRedirect(route('kasir.index'));
        $this->actingAs($kasir)->get('/pengaturan/backup')->assertRedirect(route('kasir.index'));
        $this->actingAs($kasir)->post('/pengaturan/toko', [])->assertRedirect(route('kasir.index'));
        $this->actingAs($kasir)->post('/pengaturan/karyawan', [])->assertRedirect(route('kasir.index'));
        $this->actingAs($kasir)->delete('/pengaturan/karyawan/1')->assertRedirect(route('kasir.index'));
        $this->actingAs($kasir)->post('/produk', [])->assertRedirect(route('kasir.index'));
        $this->actingAs($kasir)->put('/produk/1', [])->assertRedirect(route('kasir.index'));
        $this->actingAs($kasir)->delete('/produk/1')->assertRedirect(route('kasir.index'));
        $this->actingAs($kasir)->post('/kategori', [])->assertRedirect(route('kasir.index'));
        $this->actingAs($kasir)->delete('/kasbon/1')->assertRedirect(route('kasir.index'));
    }

    public function test_pengaturan_view_hides_backup_and_admin_tabs_for_kasir(): void
    {
        $kasir = $this->makeUser('kasir', 2, 'Kasir Toko');

        $response = $this->actingAs($kasir)->get('/pengaturan');

        $response->assertStatus(200);
        $response->assertDontSee('fa-bell', false);
        $response->assertSee('id="tab-profil"', false);
        $response->assertSee('id="tab-keamanan"', false);
        $response->assertDontSee('id="tab-toko"', false);
        $response->assertDontSee('id="tab-karyawan"', false);
        $response->assertDontSee('id="tab-backup"', false);
    }

    public function test_kasir_nota_and_laporan_pdf_render_without_hardcoded_errors(): void
    {
        $kasir = $this->makeUser('kasir', 2, 'Kasir Toko');

        $produk = new Produk([
            'nama_produk' => 'Beras 5Kg',
            'satuan'      => 'Sak',
        ]);

        $detail = new DetailPenjualan([
            'jumlah'              => 2,
            'harga_jual_saat_itu' => 68000,
            'subtotal'            => 136000,
        ]);
        $detail->setRelation('produk', $produk);

        $penjualan = new Penjualan([
            'nomor_nota'    => 'INV-20261113-0001',
            'tanggal'       => now(),
            'total_belanja' => 136000,
            'diskon'        => 0,
            'total_akhir'   => 136000,
            'metode_bayar'  => 'tunai',
            'status_bayar'  => 'lunas',
            'uang_diterima' => 150000,
            'kembalian'     => 14000,
        ]);
        $penjualan->setRelation('karyawan', $kasir);
        $penjualan->setRelation('pelanggan', new Pelanggan(['nama_pelanggan' => 'Umum']));
        $penjualan->setRelation('detail', collect([$detail]));

        $notaHtml = View::make('kasir.nota', compact('penjualan'))->render();
        $this->assertStringContainsString('INV-20261113-0001', $notaHtml);
        $this->assertStringContainsString('Beras 5Kg', $notaHtml);
        $this->assertStringContainsString('di TOKO INA', $notaHtml);

        $pdfHtml = View::make('laporan.pdf', [
            'rekapPenjualan' => collect([$penjualan]),
            'startDate'      => '2026-11-01',
            'endDate'        => '2026-11-13',
        ])->render();
        $this->assertStringContainsString('Laporan Penjualan Toko Ina', $pdfHtml);
        $this->assertStringContainsString('INV-20261113-0001', $pdfHtml);
    }
}
