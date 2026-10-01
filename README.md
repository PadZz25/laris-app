# 🏪 LARIS — Sistem Informasi Manajemen Toko Ina

Aplikasi POS (Point of Sale) & manajemen operasional untuk **Toko Ina Desa Wates**.  
Dibangun dengan **Laravel 12** + **MySQL**.

## ✨ Fitur Utama

- 🛒 **Kasir / POS** — Transaksi penjualan cepat dengan dukungan barcode
- 📖 **Buku Kasbon** — Pencatatan utang/piutang warga
- 📦 **Barang & Stok** — Manajemen produk, kategori, dan stock opname
- 🚚 **Pasokan Supplier** — Pencatatan barang masuk dari distributor
- 💸 **Pengeluaran Operasional** — Pencatatan kas keluar toko
- 📊 **Laporan & Omzet** — Rekap penjualan, export PDF/Excel

## 🛠️ Tech Stack

- **Framework:** Laravel 12
- **Database:** MySQL 8.x
- **Frontend:** Blade + CSS murni + FontAwesome
- **PHP:** 8.2+

## 🚀 Instalasi

### Prasyarat

- PHP 8.2+
- Composer 2.x
- MySQL 8.x
- Node.js 18+ (opsional, untuk asset build)

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/USERNAME/toko-ina.git
cd toko-ina

# 2. Install dependency
composer install

# 3. Copy file environment
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Konfigurasi database di .env
# Edit DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 6. Import schema database
# Import file db_toko_ina_master.sql ke MySQL

# 7. Jalankan migration (untuk kolom tambahan)
php artisan migrate

# 8. Seed akun default
php artisan db:seed --class=KaryawanSeeder

# 9. Jalankan server
php artisan serve