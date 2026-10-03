<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenjualan extends Model
{
    protected $table = 'detail_penjualan';
    protected $primaryKey = 'id_detail_penjualan';
    public $timestamps = false;

    protected $fillable = [
        'id_penjualan', 'id_produk', 'jumlah',
        'harga_jual_saat_itu', 'hpp_saat_itu', 'subtotal',
    ];

    protected $casts = [
        'jumlah'              => 'decimal:2',
        'harga_jual_saat_itu' => 'decimal:2',
        'hpp_saat_itu'        => 'decimal:2',
        'subtotal'            => 'decimal:2',
    ];

    public function penjualan(): BelongsTo
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan', 'id_penjualan');
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}