<?php

namespace App\Exports;

use App\Models\Penjualan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanPenjualanExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return Penjualan::with(['pelanggan', 'karyawan'])
            ->whereBetween(\DB::raw('DATE(tanggal)'), [$this->startDate, $this->endDate])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No. Nota',
            'Tanggal & Waktu',
            'Kasir',
            'Pelanggan',
            'Total Belanja (Rp)',
            'Metode Bayar',
            'Status'
        ];
    }

    public function map($penjualan): array
    {
        return [
            $penjualan->nomor_nota,
            \Carbon\Carbon::parse($penjualan->tanggal)->format('d/m/Y H:i'),
            $penjualan->karyawan->nama_karyawan ?? '-',
            $penjualan->pelanggan->nama_pelanggan ?? 'Umum',
            $penjualan->total_akhir,
            strtoupper($penjualan->metode_bayar),
            $penjualan->status_bayar === 'lunas' ? 'LUNAS' : 'BELUM LUNAS',
        ];
    }
}