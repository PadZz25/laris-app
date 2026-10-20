# LARIS (Layanan Aplikasi Ritel & Inventaris Sistem)

LARIS adalah sistem informasi manajemen operasional berbasis web yang dirancang khusus untuk UMKM ritel, dengan studi kasus **Toko Ina** (toko kelontong di Desa Wates, Kabupaten Blitar). Aplikasi ini bertujuan untuk mendigitalkan seluruh proses bisnis toko, mulai dari transaksi kasir, pencatatan stok, pengelolaan kasbon warga, hingga analisis laporan keuangan.

Dengan antarmuka yang bersih dan alur kerja yang dioptimalkan, LARIS membantu pemilik toko dan kasir bekerja lebih cepat, mengurangi kesalahan pencatatan manual, dan memberikan wawasan berbasis data.

## Fitur Utama

- **Point of Sale (POS):** Transaksi kasir yang responsif dengan dukungan multi-metode pembayaran (Tunai, Transfer, Kasbon) dan cetak struk.
- **Manajemen Inventaris:** Pengelolaan data produk, kategori, unggah foto otomatis (dengan optimasi konversi gambar), serta fitur penyesuaian stok (Stock Opname).
- **Buku Kasbon:** Pencatatan utang-piutang warga sekitar secara terstruktur, dilengkapi fitur pelunasan bertahap (cicilan) atau lunas penuh.
- **Pasokan Supplier:** Pencatatan barang masuk dari distributor dengan pembaruan stok otomatis yang terintegrasi.
- **Pengeluaran Operasional:** Pencatatan biaya operasional toko di luar pembelian barang dagangan agar arus kas tetap terpantau.
- **Laporan & Analitik:** Dasbor interaktif dengan grafik tren penjualan, kalkulasi laba kotor, daftar produk terlaris, serta ekspor laporan (PDF & Excel).

## Teknologi yang Digunakan

- **Framework Backend:** Laravel 12 (PHP 8.2+)
- **Database:** MySQL 8
- **Frontend:** Blade Templating, CSS Modular, Vanilla JavaScript
- **Library Pendukung:** ApexCharts, Intervention Image, Laravel Excel, DomPDF.

## Arsitektur Modul

Aplikasi ini dibangun secara modular untuk memudahkan pemeliharaan:
1. **Modul Produk & Stok:** Mengelola master data barang dan riwayat stok.
2. **Modul Kasir (POS):** Menangani antarmuka transaksi dan kalkulasi keranjang.
3. **Modul Kasbon:** Mengelola data pelanggan tetap dan piutang toko.
4. **Modul Supplier:** Mencatat rantai pasok masuk (restock) dan tagihan distributor.
5. **Modul Pengeluaran:** Mencatat biaya operasional harian toko.
6. **Modul Laporan:** Menyajikan rekapitulasi dan visualisasi data finansial.

## Panduan Instalasi (Lokal)

Ikuti langkah-langkah standar berikut untuk menjalankan proyek di lingkungan lokal Anda:

### Prasyarat
- PHP 8.2 atau lebih baru
- Composer
- MySQL Database
- Web Server (Apache/Nginx)

### Langkah-langkah

1. **Clone repository ini**
       git clone https://github.com/PadZz25/laris-app.git
       cd laris-app

2. **Install dependensi PHP**
       composer install

3. **Salin file environment**
       cp .env.example .env

4. **Generate application key**
       php artisan key:generate

5. **Konfigurasi Database**
   Buka file `.env` dan sesuaikan pengaturan koneksi database dengan lingkungan lokal Anda (sesuaikan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`).

6. **Jalankan Migrasi Database**
   Proyek ini menyertakan file migrasi standar untuk membangun struktur tabel:
       php artisan migrate

7. **Buat Tautan Penyimpanan**
   Tautan ini diperlukan agar foto produk yang diunggah dapat diakses secara publik:
       php artisan storage:link

8. **Jalankan Server Lokal**
       php artisan serve
   Aplikasi dapat diakses melalui `http://localhost:8000`.

> **Catatan Keamanan:** Konfigurasi kredensial dan data *dummy* bersifat privat. Untuk kebutuhan pengujian lokal, silakan gunakan data *seeder* yang telah disediakan (jika ada) atau hubungi pengembang untuk akses ke lingkungan pengembangan.

### Lisensi & Hak Cipta

Proyek ini dikembangkan untuk keperluan akademik dan operasional mitra.
© 2026 Tim Pengembang LARIS - Universitas Negeri Malang.
