<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';
    protected $guarded = [];

    /**
     * Ambil pengaturan toko secara aman tanpa melempar error jika tabel belum tersedia,
     * sekaligus menyinkronkan logo toko dari media_manifest.json hasil git pull.
     */
    public static function current(): ?self
    {
        try {
            $toko = static::first();

            $manifestPath = storage_path('app/public/media_manifest.json');
            if (!is_file($manifestPath)) {
                $manifestPath = public_path('storage/media_manifest.json');
            }

            if (is_file($manifestPath)) {
                $manifest = json_decode((string) file_get_contents($manifestPath), true);
                $tokoManifest = $manifest['toko'] ?? null;
                $logo = $tokoManifest['logo'] ?? null;

                if (!empty($logo)) {
                    Produk::syncPublicStorageCopy($logo);
                    $logoExists = is_file(storage_path('app/public/' . $logo))
                        || is_file(public_path('storage/' . $logo));

                    if ($logoExists) {
                        if (!$toko) {
                            $toko = static::create([
                                'id'         => 1,
                                'nama_toko'  => $tokoManifest['nama_toko'] ?? 'TOKO INA',
                                'alamat'     => $tokoManifest['alamat'] ?? 'Desa Wates, Kec. Wates, Blitar',
                                'no_telepon' => $tokoManifest['no_telepon'] ?? '0812-3456-7890',
                                'logo'       => $logo,
                            ]);
                        } elseif ($toko->logo !== $logo) {
                            $toko->update(['logo' => $logo]);
                        }
                    }
                }
            }

            return $toko;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
