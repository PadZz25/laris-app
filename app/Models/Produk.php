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
}