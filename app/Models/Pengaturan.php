<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';
    protected $guarded = [];

    /**
     * Ambil pengaturan toko secara aman tanpa melempar error jika tabel belum tersedia.
     */
    public static function current(): ?self
    {
        try {
            return static::first();
        } catch (\Throwable $e) {
            return null;
        }
    }
}