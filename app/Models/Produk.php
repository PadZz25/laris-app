<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';
    public $timestamps = false;

    protected $fillable = [
        'id_kategori',
        'nama_produk',
        'barcode',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok_sekarang',
        'min_stok',
        'foto_produk',
    ];

    protected $casts = [
        'harga_beli'    => 'decimal:2',
        'harga_jual'    => 'decimal:2',
        'stok_sekarang' => 'integer',
        'min_stok'      => 'integer',
    ];

    /**
     * Relasi: produk dimiliki oleh 1 kategori.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriProduk::class, 'id_kategori', 'id_kategori');
    }

    /**
     * Relasi: produk muncul di banyak detail_penjualan.
     */
    public function detailPenjualan(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'id_produk', 'id_produk');
    }

    /**
     * Relasi: produk muncul di banyak detail_pembelian (ON DELETE RESTRICT).
     */
    public function detailPembelian(): HasMany
    {
        return $this->hasMany(DetailPembelian::class, 'id_produk', 'id_produk');
    }

    // ─────────────────────────────────────────────
    // SCOPE — query pembantu yang sering dipakai
    // ─────────────────────────────────────────────

    /**
     * Scope: cari produk yang stoknya menipis (<= min_stok).
     */
    public function scopeStokMenipis($query)
    {
        return $query->whereColumn('stok_sekarang', '<=', 'min_stok');
    }

    /**
     * Scope: cari produk yang stoknya habis.
     */
    public function scopeStokHabis($query)
    {
        return $query->where('stok_sekarang', '<=', 0);
    }

    // ─────────────────────────────────────────────
    // HELPER — method pembantu
    // ─────────────────────────────────────────────

    /**
     * Hitung margin keuntungan (persen).
     */
    public function marginPersen(): float
    {
        if ($this->harga_beli <= 0) return 0;
        return round((($this->harga_jual - $this->harga_beli) / $this->harga_beli) * 100, 1);
    }

    /**
     * Cek apakah stok menipis.
     */
    public function isStokMenipis(): bool
    {
        return $this->stok_sekarang <= $this->min_stok;
    }

    /**
     * Cek apakah stok habis.
     */
    public function isStokHabis(): bool
    {
        return $this->stok_sekarang <= 0;
    }

    // ─────────────────────────────────────────────
    // SINKRONISASI MEDIA VIA GIT (MANIFEST & STORAGE)
    // ─────────────────────────────────────────────

    /**
     * Pastikan file media tersedia di storage/app/public dan public/storage
     * (berguna jika di komputer rekan tim public/storage bukan symlink/junction).
     */
    public static function syncPublicStorageCopy(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $storagePath  = storage_path('app/public/' . $relativePath);
        $publicPath   = public_path('storage/' . $relativePath);

        $storageRootReal = realpath(storage_path('app/public'));
        $publicRootReal  = realpath(public_path('storage'));

        // Jika public/storage sudah berupa symlink/junction ke storage/app/public, tidak perlu copy
        if ($storageRootReal && $publicRootReal && $storageRootReal === $publicRootReal) {
            return;
        }

        if (is_file($storagePath) && !is_file($publicPath)) {
            $dir = dirname($publicPath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            @copy($storagePath, $publicPath);
        } elseif (is_file($publicPath) && !is_file($storagePath)) {
            $dir = dirname($storagePath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            @copy($publicPath, $storagePath);
        } elseif (is_file($storagePath) && is_file($publicPath)) {
            if (@md5_file($storagePath) !== @md5_file($publicPath)) {
                $source = (@filemtime($storagePath) >= @filemtime($publicPath)) ? $storagePath : $publicPath;
                $target = ($source === $storagePath) ? $publicPath : $storagePath;
                @copy($source, $target);
            }
        }
    }

    /**
     * Hapus file media dari storage/app/public dan public/storage.
     */
    public static function deletePublicStorageCopy(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $storagePath  = storage_path('app/public/' . $relativePath);
        $publicPath   = public_path('storage/' . $relativePath);

        if (is_file($storagePath)) {
            @unlink($storagePath);
        }
        if (is_file($publicPath)) {
            @unlink($publicPath);
        }
    }

    /**
     * Simpan daftar pemetaan foto_produk & logo toko ke storage/app/public/media_manifest.json
     * agar saat di-push ke GitHub dan di-pull rekan tim, database lokal rekan tim ikut tersinkron.
     */
    public static function updateMediaManifest(): void
    {
        try {
            $produkItems = static::orderBy('id_produk')
                ->get(['id_produk', 'barcode', 'nama_produk', 'foto_produk'])
                ->map(function (self $p) {
                    if ($p->foto_produk) {
                        static::syncPublicStorageCopy($p->foto_produk);
                    }
                    return [
                        'id_produk'   => (int) $p->id_produk,
                        'barcode'     => $p->barcode,
                        'nama_produk' => $p->nama_produk,
                        'foto_produk' => $p->foto_produk,
                    ];
                })
                ->values()
                ->all();

            $toko = Pengaturan::query()->first();
            if ($toko && $toko->logo) {
                static::syncPublicStorageCopy($toko->logo);
            }

            $manifest = [
                'toko' => $toko ? [
                    'nama_toko'  => $toko->nama_toko,
                    'alamat'     => $toko->alamat,
                    'no_telepon' => $toko->no_telepon,
                    'logo'       => $toko->logo,
                ] : null,
                'produk' => $produkItems,
            ];

            $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
            $manifestStoragePath = storage_path('app/public/media_manifest.json');
            $dir = dirname($manifestStoragePath);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            file_put_contents($manifestStoragePath, $json);
            static::syncPublicStorageCopy('media_manifest.json');
        } catch (\Throwable $e) {
            // Abaikan jika database belum siap
        }
    }

    /**
     * Baca media_manifest.json dan sinkronkan kolom foto_produk ke database lokal
     * jika ada produk di database lokal yang foto_produk-nya belum sesuai dengan file hasil git pull.
     */
    public static function syncFromMediaManifest(): void
    {
        try {
            static::syncPublicStorageCopy('media_manifest.json');
            $manifestPath = storage_path('app/public/media_manifest.json');
            if (!is_file($manifestPath)) {
                $manifestPath = public_path('storage/media_manifest.json');
            }
            if (!is_file($manifestPath)) {
                return;
            }

            $manifest = json_decode((string) file_get_contents($manifestPath), true);
            if (!is_array($manifest) || empty($manifest['produk']) || !is_array($manifest['produk'])) {
                return;
            }

            $allProduk   = static::all(['id_produk', 'barcode', 'foto_produk']);
            $byId        = $allProduk->keyBy('id_produk');
            $byBarcode   = $allProduk->whereNotNull('barcode')->keyBy('barcode');

            foreach ($manifest['produk'] as $item) {
                $foto = $item['foto_produk'] ?? null;
                if (empty($foto)) {
                    continue;
                }

                static::syncPublicStorageCopy($foto);

                $fileExists = is_file(storage_path('app/public/' . $foto))
                    || is_file(public_path('storage/' . $foto));

                if (!$fileExists) {
                    continue;
                }

                $target = null;
                if (!empty($item['id_produk']) && isset($byId[$item['id_produk']])) {
                    $target = $byId[$item['id_produk']];
                } elseif (!empty($item['barcode']) && isset($byBarcode[$item['barcode']])) {
                    $target = $byBarcode[$item['barcode']];
                }

                if ($target && $target->foto_produk !== $foto) {
                    static::where('id_produk', $target->id_produk)->update(['foto_produk' => $foto]);
                    $target->foto_produk = $foto;
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika database belum terkoneksi (misal saat unit test tanpa DB)
        }
    }
}
