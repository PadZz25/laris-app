<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    protected $table = 'detail_penjualan';
    protected $primaryKey = 'id_detail_penjualan';
    public $timestamps = false;

    protected $fillable = [
        'id_penjualan',
        'id_produk',
        'jumlah',
        'harga_jual_saat_itu',
        'hpp_saat_itu',
        'subtotal',
    ];
}