<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';
    protected $primaryKey = 'id_pengeluaran';
    public $timestamps = false;

    protected $fillable = [
        'id_karyawan',
        'nama_pengeluaran',
        'jumlah',
        'tanggal',
        'kategori',
        'sumber_dana',
    ];

    protected $casts = [
        'jumlah'  => 'decimal:2',
        'tanggal' => 'datetime',
    ];

    /**
     * Relasi: pengeluaran dicatat oleh 1 karyawan.
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    /**
     * Scope: filter pengeluaran bulan berjalan.
     */
    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal', now()->month)
                     ->whereYear('tanggal', now()->year);
    }

    /**
     * Scope: filter pengeluaran hari ini.
     */
    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', today());
    }
}