<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPembelian extends Model
{
    protected $table = 'detail_pembelian';
    protected $primaryKey = 'id_detail_pembelian';
    public $timestamps = false;

    protected $fillable = [
        'id_pembelian', 'id_produk', 'jumlah',
        'harga_beli_saat_itu', 'subtotal',
    ];

    protected $casts = [
        'jumlah'              => 'decimal:2',
        'harga_beli_saat_itu' => 'decimal:2',
        'subtotal'            => 'decimal:2',
    ];

    public function pembelian(): BelongsTo
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian', 'id_pembelian');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}