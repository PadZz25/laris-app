<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjualan extends Model
{
    protected $table = 'penjualan';
    protected $primaryKey = 'id_penjualan';
    public $timestamps = false;

    protected $fillable = [
        'nomor_nota', 'id_karyawan', 'id_pelanggan', 'tanggal',
        'total_belanja', 'diskon', 'total_akhir',
        'metode_bayar', 'status_bayar', 'uang_diterima', 'kembalian',
    ];

    protected $casts = [
        'tanggal'       => 'datetime',
        'total_belanja' => 'decimal:2',
        'total_akhir'   => 'decimal:2',
        'diskon'        => 'decimal:2',
    ];

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPenjualan::class, 'id_penjualan', 'id_penjualan');
    }
}