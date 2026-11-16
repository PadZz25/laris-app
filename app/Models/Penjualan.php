<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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

    /**
     * Filter transaksi berdasarkan rentang tanggal (YYYY-MM-DD, inklusif).
     */
    public function scopeBetweenDates($query, string $startDate, string $endDate)
    {
        return $query->whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate]);
    }

    /**
     * Normalisasi rentang tanggal laporan dengan default 7 hari terakhir.
     */
    public static function resolveDateRange(?string $startInput, ?string $endInput): array
    {
        $defaultStart = Carbon::now()->subDays(6)->format('Y-m-d');
        $defaultEnd   = Carbon::now()->format('Y-m-d');

        $startDate = static::parseDateOrDefault($startInput, $defaultStart);
        $endDate   = static::parseDateOrDefault($endInput, $defaultEnd);

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        return [$startDate, $endDate];
    }

    private static function parseDateOrDefault(?string $value, string $fallback): string
    {
        if (empty($value)) {
            return $fallback;
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $value);
            if ($parsed && $parsed->format('Y-m-d') === $value) {
                return $value;
            }
        } catch (\Throwable $e) {
            // Gunakan fallback jika format tanggal tidak valid
        }

        return $fallback;
    }

    /**
     * Format payload detail transaksi untuk modal rincian & cetak ulang struk.
     */
    public function toDetailPayload(): array
    {
        $this->loadMissing(['detail.produk', 'pelanggan', 'karyawan']);

        return [
            'id_penjualan'  => $this->id_penjualan,
            'nomor_nota'    => $this->nomor_nota,
            'tanggal'       => Carbon::parse($this->tanggal)->format('d M Y, H:i'),
            'kasir'         => $this->karyawan->nama_karyawan ?? '-',
            'pelanggan'     => $this->pelanggan->nama_pelanggan ?? 'Umum',
            'total_belanja' => (float) ($this->total_belanja ?? $this->total_akhir),
            'diskon'        => (float) ($this->diskon ?? 0),
            'total_akhir'   => (float) $this->total_akhir,
            'metode_bayar'  => $this->metode_bayar,
            'status_bayar'  => $this->status_bayar,
            'items'         => $this->detail->map(function ($item) {
                return [
                    'nama_produk' => $item->produk->nama_produk ?? '-',
                    'jumlah'      => (float) $item->jumlah,
                    'harga_jual'  => (float) $item->harga_jual_saat_itu,
                    'subtotal'    => (float) $item->subtotal,
                    'satuan'      => $item->produk->satuan ?? '',
                ];
            }),
        ];
    }
}