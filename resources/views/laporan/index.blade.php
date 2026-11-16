@extends('layouts.app')

@section('title', 'Laporan & Omzet')
@section('page-title', 'Laporan & Omzet')

@section('content')
<div class="page-scroll">
    <div class="laporan-wrapper">

        {{-- FILTER BAR & EXPORT --}}
        @include('partials.periode_filter_bar', [
            'action'      => route('laporan.index'),
            'startDate'   => $startDate,
            'endDate'     => $endDate,
            'filterTitle' => 'Periode Laporan',
            'showExports' => true,
        ])

        {{-- KPI CARDS (5 Items) --}}
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-title">Total Omzet</div>
                <div class="kpi-value">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Laba Kotor</div>
                <div class="kpi-value success">Rp {{ number_format($labaKotor, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Uang Masuk</div>
                <div class="kpi-value info">Rp {{ number_format($uangMasuk, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Piutang Baru</div>
                <div class="kpi-value danger">Rp {{ number_format($piutangBaru, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Stok Menipis</div>
                <div class="kpi-value warning">{{ $stokMenipis }} Produk</div>
            </div>
        </div>

        {{-- CHARTS SECTION (2 Kolom) --}}
        <div class="laporan-charts-grid">
            
            {{-- KIRI: Tren Omzet --}}
            <div class="chart-card">
                <div class="chart-title"><i class="fa-solid fa-chart-line icon-line"></i> Tren Omzet Harian</div>
                <div id="chartTrenOmzet" class="chart-area-box"></div>
            </div>

            {{-- KANAN: Kategori & Metode (Ditumpuk) --}}
            <div class="laporan-charts-side">
                <div class="chart-card">
                    <div class="chart-title"><i class="fa-solid fa-chart-pie icon-pie"></i> Kategori Terlaris</div>
                    <div id="chartKategori" class="chart-donut-box"></div>
                </div>
                <div class="chart-card">
                    <div class="chart-title"><i class="fa-solid fa-wallet icon-wallet"></i> Metode Bayar</div>
                    <div id="chartMetode" class="chart-donut-box"></div>
                </div>
            </div>
        </div>

        {{-- TABLES SECTION (3 Kolom) --}}
        <div class="laporan-tables-grid">
            
            {{-- 1. Tabel Rekap Penjualan --}}
            <div class="table-card laporan-table-card">
                <div class="laporan-table-header">Transaksi Terbaru</div>
                <div class="table-card-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nota & Waktu</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapPenjualan as $trx)
                                <tr class="laporan-trx-row" onclick="showDetailTransaksi({{ $trx->id_penjualan }})" title="Klik untuk melihat rincian">
                                    <td>
                                        <div class="laporan-nota-code">{{ $trx->nomor_nota }}</div>
                                        <div class="laporan-nota-time">{{ \Carbon\Carbon::parse($trx->tanggal)->format('d M, H:i') }}</div>
                                    </td>
                                    <td>{{ $trx->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                                    <td class="laporan-cell-total">Rp {{ number_format($trx->total_akhir, 0, ',', '.') }}</td>
                                    <td>
                                        @if($trx->status_bayar == 'lunas')
                                            <span class="badge badge-safe">Lunas</span>
                                        @else
                                            <span class="badge badge-danger">Kasbon</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="laporan-empty-cell">Belum ada transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. Tabel Top 5 Produk --}}
            <div class="table-card laporan-table-card">
                <div class="laporan-table-header info">
                    <i class="fa-solid fa-fire"></i> 5 Produk Terlaris
                </div>
                <div class="table-card-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Terjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProduk as $tp)
                                <tr>
                                    <td class="laporan-cell-bold">{{ $tp->nama_produk }}</td>
                                    <td class="laporan-cell-info">{{ (int)$tp->total_qty }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="laporan-empty-cell">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. Tabel Stok Menipis --}}
            <div class="table-card laporan-table-card">
                <div class="laporan-table-header danger">
                    <i class="fa-solid fa-triangle-exclamation"></i> Reorder Stok
                </div>
                <div class="table-card-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($daftarStokMenipis as $prd)
                                <tr>
                                    <td class="laporan-cell-bold">{{ $prd->nama_produk }}</td>
                                    <td class="laporan-cell-danger">{{ $prd->stok_sekarang }} {{ $prd->satuan }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="laporan-empty-cell">Semua stok aman.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@include('partials.detail_transaksi_modal', [
    'detailUrlPrefix' => url('/laporan/transaksi'),
])
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // --- 1. Grafik Tren Omzet ---
    var optionsTren = {
        series: [{ name: 'Omzet', data: @json($chartTotals) }],
        chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
        colors: ['#0284c7'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05, stops: [0, 90, 100] } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        markers: { size: 5, colors: ['#ffffff'], strokeColors: '#0284c7', strokeWidth: 2 },
        xaxis: { categories: @json($chartDates), axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { min: 0, labels: { formatter: function (val) { return "Rp " + val.toLocaleString('id-ID'); } } },
        tooltip: { y: { formatter: function (val) { return "Rp " + val.toLocaleString('id-ID'); } } }
    };
    new ApexCharts(document.querySelector("#chartTrenOmzet"), optionsTren).render();

    // --- 3. Grafik Kategori ---
    var optionsKategori = {
        series: @json($chartKategoriSeries),
        labels: @json($chartKategoriLabels),
        chart: { type: 'donut', height: 200, fontFamily: 'Plus Jakarta Sans, sans-serif' },
        colors: ['#0284c7', '#8b5cf6', '#f59e0b', '#10b981', '#ef4444'],
        plotOptions: { 
            pie: { 
                donut: { 
                    size: '65%',
                    labels: { 
                        show: true,
                        name: { show: true, fontSize: '11px', color: '#64748b' },
                        value: { 
                            show: true, fontSize: '13px', fontWeight: 800, color: '#0f172a',
                            formatter: function (val) { return "Rp " + parseInt(val).toLocaleString('id-ID'); }
                        },
                        total: { 
                            show: true, showAlways: true, label: 'Omzet', fontSize: '11px', color: '#64748b',
                            formatter: function (w) { 
                                return 'Rp ' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('id-ID'); 
                            }
                        } 
                    } 
                } 
            } 
        },
        legend: { position: 'bottom', fontSize: '11px' },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: function (val) { return "Rp " + val.toLocaleString('id-ID'); } } }
    };
    new ApexCharts(document.querySelector("#chartKategori"), optionsKategori).render();

    // --- 4. Grafik Metode Pembayaran ---
    var optionsMetode = {
        series: @json($chartMetodeSeries),
        labels: @json($chartMetodeLabels),
        chart: { type: 'donut', height: 200, fontFamily: 'Plus Jakarta Sans, sans-serif' },
        colors: ['#16a34a', '#0284c7', '#dc2626'], 
        plotOptions: { 
            pie: { 
                donut: { 
                    size: '65%', 
                    labels: { 
                        show: true,
                        name: { show: true, fontSize: '11px', color: '#64748b' },
                        value: { 
                            show: true, fontSize: '13px', fontWeight: 800, color: '#0f172a',
                            formatter: function (val) { return "Rp " + parseInt(val).toLocaleString('id-ID'); }
                        },
                        total: { 
                            show: true, showAlways: true, label: 'Total', fontSize: '11px', color: '#64748b',
                            formatter: function (w) { 
                                return 'Rp ' + w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('id-ID'); 
                            }
                        } 
                    } 
                } 
            } 
        },
        legend: { position: 'bottom', fontSize: '11px' },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: function (val) { return "Rp " + val.toLocaleString('id-ID'); } } }
    };
    new ApexCharts(document.querySelector("#chartMetode"), optionsMetode).render();
</script>
@endpush
