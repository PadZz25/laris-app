<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $timestamps = false;

    protected $fillable = [
        'nama_pelanggan',
        'no_telepon',
        'alamat',
        'total_hutang',
    ];

    protected $casts = [
        'total_hutang' => 'decimal:2',
    ];

    public function penjualan(): HasMany
    {
        return $this->hasMany(Penjualan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function getStatusPiutangAttribute(): string
    {
        return $this->total_hutang > 0 ? 'Ada Kasbon' : 'Bebas Utang';
    }
}