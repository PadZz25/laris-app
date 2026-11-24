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
    }

    public function test_kasir_can_manage_warga_on_kasbon_routes_and_view(): void
    {
        $routes = app('router')->getRoutes();
        $updateMiddleware = $routes->getByName('kasbon.update')->gatherMiddleware();
        $destroyMiddleware = $routes->getByName('kasbon.destroy')->gatherMiddleware();

        $this->assertContains('auth', $updateMiddleware);
        $this->assertNotContains('role:admin', $updateMiddleware);
        $this->assertContains('auth', $destroyMiddleware);
        $this->assertNotContains('role:admin', $destroyMiddleware);

        $kasir = $this->makeUser('kasir', 2, 'Kasir Toko');
        $this->actingAs($kasir);

        $warga = new Pelanggan([
            'nama_pelanggan' => 'Pak Budi',
            'no_telepon'     => '08123456789',
            'alamat'         => 'RT 01',
            'total_hutang'   => 0,
        ]);
        $warga->id_pelanggan = 10;

        $html = View::make('kasbon.index', [
            'errors'    => new \Illuminate\Support\ViewErrorBag(),
            'pelanggan' => collect([$warga]),
            'stats'     => [
                'total_warga'   => 1,
                'warga_kasbon'  => 0,
                'warga_bebas'   => 1,
                'total_piutang' => 0,
            ],
        ])->render();

        $this->assertStringContainsString('title="Edit Warga"', $html);
        $this->assertStringContainsString('title="Hapus Warga"', $html);
        $this->assertStringContainsString('openEditWarga(this)', $html);
        $this->assertStringContainsString('openHapusWarga(this)', $html);
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

    public function test_kasir_view_defines_global_helpers_before_pos_init_scripts(): void
    {
        $kasir = $this->makeUser('kasir', 2, 'Kasir Toko');
        $this->actingAs($kasir);

        $html = View::make('kasir.index', [
            'produkData'    => [[
                'id'       => 1,
                'nama'     => 'Beras 5Kg',
                'barcode'  => 'BRS-01',
                'kategori' => 'Sembako',
                'harga'    => 68000,
                'stok'     => 10,
                'satuan'   => 'Sak',
                'foto'     => null,
            ]],
            'kategoriList'  => collect([]),
            'pelangganList' => collect([]),
        ])->render();

        $escapePos = strpos($html, 'function escapeHtml(');
        $toastPos  = strpos($html, 'function showToast(');
        $initPos   = strpos($html, 'loadCart();');

        $this->assertNotFalse($escapePos);
        $this->assertNotFalse($toastPos);
        $this->assertNotFalse($initPos);
        $this->assertLessThan($initPos, $escapePos);
        $this->assertLessThan($initPos, $toastPos);
        $this->assertStringContainsString('id="posKategori"', $html);
        $this->assertStringNotContainsString('id="posCats"', $html);
    }

    public function test_kasir_laporan_routes_and_sidebar_are_properly_configured(): void
    {
        $routes = app('router')->getRoutes();
        $kasirLaporanMiddleware = $routes->getByName('kasir.laporan')->gatherMiddleware();
        $kasirDetailMiddleware  = $routes->getByName('kasir.laporan.detail')->gatherMiddleware();
        $adminLaporanMiddleware = $routes->getByName('laporan.index')->gatherMiddleware();

        $this->assertContains('auth', $kasirLaporanMiddleware);
        $this->assertNotContains('role:admin', $kasirLaporanMiddleware);
        $this->assertContains('auth', $kasirDetailMiddleware);
        $this->assertNotContains('role:admin', $kasirDetailMiddleware);
        $this->assertContains('role:admin', $adminLaporanMiddleware);

        $kasir = $this->makeUser('kasir', 2, 'Kasir Toko');
        $this->actingAs($kasir);
        $kasirSidebar = View::make('partials.sidebar')->render();
        $this->assertStringContainsString(route('kasir.laporan'), $kasirSidebar);
        $this->assertStringContainsString('Laporan', $kasirSidebar);
        $this->assertStringNotContainsString('Laporan &amp; Omzet', $kasirSidebar);
        $this->assertStringNotContainsString(route('laporan.index'), $kasirSidebar);

        $admin = $this->makeUser('admin', 1, 'ina');
        $this->actingAs($admin);
        $adminSidebar = View::make('partials.sidebar')->render();
        $this->assertStringContainsString(route('laporan.index'), $adminSidebar);
        $this->assertStringContainsString('Laporan &amp; Omzet', $adminSidebar);
        $this->assertStringNotContainsString(route('kasir.laporan'), $adminSidebar);
    }

    public function test_kasir_laporan_view_renders_history_transaksi_and_detail_modal(): void
    {
        $kasir = $this->makeUser('kasir', 2, 'Kasir Toko');
        $this->actingAs($kasir);

        $penjualan = new Penjualan([
            'nomor_nota'    => 'INV-20261116-0001',
            'tanggal'       => '2026-11-16 10:15:00',
            'total_belanja' => 50000,
            'diskon'        => 0,
            'total_akhir'   => 50000,
            'metode_bayar'  => 'tunai',
            'status_bayar'  => 'lunas',
        ]);
        $penjualan->id_penjualan = 99;
        $penjualan->setRelation('karyawan', $kasir);
        $penjualan->setRelation('pelanggan', new Pelanggan(['nama_pelanggan' => 'Umum']));

        $html = View::make('kasir.laporan', [
            'errors'           => new \Illuminate\Support\ViewErrorBag(),
            'riwayatTransaksi' => collect([$penjualan]),
            'startDate'        => '2026-11-10',
            'endDate'          => '2026-11-16',
        ])->render();

        $this->assertStringContainsString('<h1>Laporan</h1>', $html);
        $this->assertStringContainsString('History Transaksi', $html);
        $this->assertStringContainsString('INV-20261116-0001', $html);
        $this->assertStringContainsString('showDetailTransaksi(99)', $html);
        $this->assertStringContainsString('/kasir/nota/99', $html);
        $this->assertStringContainsString('id="modalDetailTransaksi"', $html);
        $this->assertStringContainsString('/kasir/laporan/transaksi', $html);

        // Halaman laporan kasir hanya menampilkan riwayat transaksi, bukan KPI omzet / grafik admin
        $this->assertStringNotContainsString('Total Omzet', $html);
        $this->assertStringNotContainsString('Laba Kotor', $html);
        $this->assertStringNotContainsString('ApexCharts', $html);
        $this->assertStringNotContainsString('export-excel', $html);
    }

    public function test_penjualan_date_range_and_detail_payload_helpers(): void
    {
        [$start, $end] = Penjualan::resolveDateRange('2026-11-16', '2026-11-10');
        $this->assertSame('2026-11-10', $start);
        $this->assertSame('2026-11-16', $end);

        [$fallbackStart, $fallbackEnd] = Penjualan::resolveDateRange('bukan-tanggal', '');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $fallbackStart);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $fallbackEnd);

        $kasir = $this->makeUser('kasir', 2, 'Kasir Toko');
        $produk = new Produk(['nama_produk' => 'Minyak Kita 1L', 'satuan' => 'Pouch']);
        $detail = new DetailPenjualan([
            'jumlah'              => 2,
            'harga_jual_saat_itu' => 16000,
            'subtotal'            => 32000,
        ]);
        $detail->setRelation('produk', $produk);

        $penjualan = new Penjualan([
            'nomor_nota'    => 'INV-20261116-0002',
            'tanggal'       => '2026-11-16 11:00:00',
            'total_belanja' => 32000,
            'diskon'        => 2000,
            'total_akhir'   => 30000,
            'metode_bayar'  => 'tunai',
            'status_bayar'  => 'lunas',
        ]);
        $penjualan->id_penjualan = 100;
        $penjualan->setRelation('karyawan', $kasir);
        $penjualan->setRelation('pelanggan', new Pelanggan(['nama_pelanggan' => 'Bu Sari']));
        $penjualan->setRelation('detail', collect([$detail]));

        $payload = $penjualan->toDetailPayload();
        $this->assertSame(100, $payload['id_penjualan']);
        $this->assertSame('INV-20261116-0002', $payload['nomor_nota']);
        $this->assertSame('Kasir Toko', $payload['kasir']);
        $this->assertSame('Bu Sari', $payload['pelanggan']);
        $this->assertSame(32000.0, $payload['total_belanja']);
        $this->assertSame(2000.0, $payload['diskon']);
        $this->assertSame(30000.0, $payload['total_akhir']);
        $this->assertCount(1, $payload['items']);
        $this->assertSame('Minyak Kita 1L', $payload['items'][0]['nama_produk']);
    }

    public function test_karyawan_seeder_does_not_contain_hardcoded_credentials(): void
    {
        $seederContent = file_get_contents(database_path('seeders/KaryawanSeeder.php'));

        $this->assertNotFalse($seederContent);
        $this->assertStringNotContainsString('password_rahasia', $seederContent);
        $this->assertStringNotContainsString("'ina'", strtolower($seederContent));
        $this->assertStringNotContainsString("'kaka'", strtolower($seederContent));
        $this->assertStringContainsString('Karyawan::exists()', $seederContent);
        $this->assertStringContainsString('SEED_ADMIN_USERNAME', $seederContent);
        $this->assertStringContainsString('SEED_ADMIN_PASSWORD', $seederContent);
    }

    public function test_login_page_uses_standard_password_manager_autocomplete_attributes(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('autocomplete="on"', false);
        $response->assertSee('for="username"', false);
        $response->assertSee('id="username"', false);
        $response->assertSee('autocomplete="username"', false);
        $response->assertSee('for="input-password"', false);
        $response->assertSee('autocomplete="current-password"', false);
        $response->assertSee('placeholder="Masukkan kata sandi..."', false);
        $response->assertDontSee('placeholder="••••••••"', false);
        $response->assertSee('aria-pressed="false"', false);
        $response->assertDontSee('tabindex="-1"', false);
    }

    public function test_produk_view_uses_kasir_style_search_and_category_select(): void
    {
        $kasir = $this->makeUser('kasir', 2, 'Kasir Toko');
        $this->actingAs($kasir);

        $kategori = new \App\Models\KategoriProduk(['nama_kategori' => 'Sembako']);
        $kategori->id_kategori = 1;

        $produk = new Produk([
            'nama_produk'   => 'Beras Mentari 5kg',
            'barcode'       => 'BRS-001',
            'satuan'        => 'Sak',
            'harga_beli'    => 62000,
            'harga_jual'    => 68000,
            'stok_sekarang' => 10,
            'min_stok'      => 5,
        ]);
        $produk->id_produk = 1;
        $produk->setRelation('kategori', $kategori);

        $html = View::make('produk.index', [
            'errors'       => new \Illuminate\Support\ViewErrorBag(),
            'produk'       => collect([$produk]),
            'kategoriList' => collect([$kategori]),
            'stats'        => [
                'total_jenis'  => 1,
                'stok_menipis' => 0,
                'stok_habis'   => 0,
                'total_aset'   => 620000,
            ],
        ])->render();

        $this->assertStringContainsString('search-wrapper search-wrapper-flex', $html);
        $this->assertStringContainsString('id="filterKategoriProduk"', $html);
        $this->assertStringContainsString('class="filter-select"', $html);
        $this->assertStringNotContainsString('id="categoryPills"', $html);
        $this->assertStringContainsString('<option value="Sembako">Sembako</option>', $html);
    }

    public function test_reverse_proxy_https_headers_and_logout_419_recovery(): void
    {
        // 1. Uji deteksi HTTPS di balik reverse proxy (mis. DomCloud/Nginx)
        $proxyResponse = $this->withHeaders([
            'Host'              => 'larisapp.domcloud.dev',
            'X-Forwarded-Host'  => 'larisapp.domcloud.dev',
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-Port'  => '443',
        ])->get('/login');

        $proxyResponse->assertStatus(200);
        $proxyResponse->assertSee('action="https://larisapp.domcloud.dev/login"', false);

        // 2. Uji GET /logout aman redirect ke login / home (bukan 405 Method Not Allowed)
        $this->get('/logout')->assertRedirect(route('login'));

        // 3. Uji penanganan TokenMismatchException (419) pada POST /logout dan /login
        $handler = app(\Illuminate\Contracts\Debug\ExceptionHandler::class);
        $tokenEx = new \Illuminate\Session\TokenMismatchException('CSRF token mismatch.');

        // Kasus A: sesi sudah habis (guest) saat klik Logout
        $guestLogoutReq = \Illuminate\Http\Request::create('/logout', 'POST');
        $guestLogoutReq->setLaravelSession(app('session.store'));
        $guestRes = $handler->render($guestLogoutReq, $tokenEx);
        $this->assertSame(302, $guestRes->getStatusCode());
        $this->assertStringContainsString('/login', (string) $guestRes->headers->get('Location'));

        // Kasus B: masih login & klik Logout dari origin yang sama namun token kedaluwarsa
        $kasir = $this->makeUser('kasir', 2, 'Kasir Toko');
        $this->actingAs($kasir);
        $authLogoutReq = \Illuminate\Http\Request::create('https://larisapp.domcloud.dev/logout', 'POST', [], [], [], [
            'HTTP_ORIGIN' => 'https://larisapp.domcloud.dev',
            'HTTPS'       => 'on',
        ]);
        $authLogoutReq->setLaravelSession(app('session.store'));
        $authRes = $handler->render($authLogoutReq, $tokenEx);
        $this->assertSame(302, $authRes->getStatusCode());
        $this->assertStringContainsString('/login', (string) $authRes->headers->get('Location'));
        $this->assertNull(auth()->user());

        // Kasus C: 419 pada POST /login diarahkan kembali ke halaman login dengan pesan error
        $loginReq = \Illuminate\Http\Request::create('/login', 'POST', ['username' => 'ina']);
        $loginReq->setLaravelSession(app('session.store'));
        $loginRes = $handler->render($loginReq, $tokenEx);
        $this->assertSame(302, $loginRes->getStatusCode());
        $this->assertStringContainsString('/login', (string) $loginRes->headers->get('Location'));
    }
}
