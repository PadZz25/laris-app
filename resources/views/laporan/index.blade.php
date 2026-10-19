@extends('layouts.app')

@section('title', 'Laporan & Omzet')
@section('page-title', 'Laporan & Omzet')

@push('styles')
<style>
    .laporan-wrapper { display: flex; flex-direction: column; gap: 16px; padding-bottom: 24px; }
    
    /* Grid KPI responsif */
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 16px; }
    .kpi-card { background: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
    .kpi-title { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; }
    .kpi-value { font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 8px; }
    
    /* Perbaikan Chart Card */
    .chart-card { 
        background: #fff; border-radius: 16px; padding: 20px; 
        box-shadow: 0 4px 6px rgba(0,0,0,0.02); 
        height: 100%;
        min-width: 0; /* SANGAT PENTING: Mencegah chart meluber ke kanan */
        display: flex;
        flex-direction: column;
    }
    .chart-title { font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    
    .filter-bar { background: #fff; border-radius: 16px; padding: 14px 20px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    
    .quick-filter { display: flex; gap: 8px; }
    .qf-btn { padding: 6px 14px; border-radius: 20px; border: 1px solid #cbd5e1; background: #fff; font-size: 11px; font-weight: 700; color: #475569; cursor: pointer; transition: 0.15s; }
    .qf-btn:hover, .qf-btn.active { background: #0284c7; color: #fff; border-color: #0284c7; }
</style>
@endpush

@section('content')
<div class="page-scroll">
    <div class="laporan-wrapper">

        {{-- FILTER BAR & EXPORT --}}
        <div class="filter-bar">
            <div style="font-weight: 800; font-size: 13px; color: #0f172a; margin-right: auto;">
                <i class="fa-solid fa-calendar-days" style="color: #429198;"></i> Periode Laporan
            </div>
            
            <form method="GET" action="{{ route('laporan.index') }}" id="formFilter" class="quick-filter" style="align-items: center;">
                <button type="button" class="qf-btn" onclick="setTanggal('hari')">Hari Ini</button>
                <button type="button" class="qf-btn" onclick="setTanggal('minggu')">Minggu Ini</button>
                <button type="button" class="qf-btn" onclick="setTanggal('bulan')">Bulan Ini</button>
                
                <input type="date" name="start_date" id="startDate" value="{{ $startDate }}" class="search-input" style="width: 130px; border-radius: 8px; padding: 6px 10px; margin-left: 10px;">
                <span style="font-size: 12px; color: #64748b;">s/d</span>
                <input type="date" name="end_date" id="endDate" value="{{ $endDate }}" class="search-input" style="width: 130px; border-radius: 8px; padding: 6px 10px;">
                <button type="submit" class="btn-primary" style="padding: 8px 16px; font-size: 12px; border-radius: 8px;">Terapkan</button>
            </form>

            <div style="display: flex; gap: 8px; border-left: 1px solid #e2e8f0; padding-left: 12px;">
                <a href="{{ route('laporan.exportExcel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-secondary" style="background: #16a34a; color: white; border: none; padding: 8px 12px; font-size: 11px;">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('laporan.exportPdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-secondary" style="background: #dc2626; color: white; border: none; padding: 8px 12px; font-size: 11px;">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>

        {{-- KPI CARDS (5 Items) --}}
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-title">Total Omzet</div>
                <div class="kpi-value">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Laba Kotor</div>
                <div class="kpi-value" style="color: #16a34a;">Rp {{ number_format($labaKotor, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Uang Masuk</div>
                <div class="kpi-value" style="color: #0284c7;">Rp {{ number_format($uangMasuk, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Piutang Baru</div>
                <div class="kpi-value" style="color: #dc2626;">Rp {{ number_format($piutangBaru, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Stok Menipis</div>
                <div class="kpi-value" style="color: #d97706;">{{ $stokMenipis }} Produk</div>
            </div>
        </div>

                {{-- CHARTS SECTION (2 Kolom) --}}
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
            
            {{-- KIRI: Tren Omzet --}}
            <div class="chart-card">
                <div class="chart-title"><i class="fa-solid fa-chart-line" style="color: #0284c7;"></i> Tren Omzet Harian</div>
                <div id="chartTrenOmzet" style="margin-top: auto; margin-bottom: auto;"></div>
            </div>

            {{-- KANAN: Kategori & Metode (Ditumpuk) --}}
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div class="chart-card" style="flex: 1;">
                    <div class="chart-title"><i class="fa-solid fa-chart-pie" style="color: #8b5cf6;"></i> Kategori Terlaris</div>
                    <div id="chartKategori" style="display: flex; justify-content: center;"></div>
                </div>
                <div class="chart-card" style="flex: 1;">
                    <div class="chart-title"><i class="fa-solid fa-wallet" style="color: #f59e0b;"></i> Metode Bayar</div>
                    <div id="chartMetode" style="display: flex; justify-content: center;"></div>
                </div>
            </div>
        </div>

        {{-- TABLES SECTION (3 Kolom) --}}
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px;">
            
            {{-- 1. Tabel Rekap Penjualan --}}
            <div class="table-card" style="height: 350px;">
                <div style="padding: 16px; border-bottom: 1px solid #e2e8f0; font-weight: 800; font-size: 14px;">Transaksi Terbaru</div>
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
                                <tr>
                                    <td>
                                        <div style="font-weight: 700;">{{ $trx->nomor_nota }}</div>
                                        <div style="font-size: 9px; color: #64748b;">{{ \Carbon\Carbon::parse($trx->tanggal)->format('d M, H:i') }}</div>
                                    </td>
                                    <td>{{ $trx->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                                    <td style="font-weight: 800;">Rp {{ number_format($trx->total_akhir, 0, ',', '.') }}</td>
                                    <td>
                                        @if($trx->status_bayar == 'lunas')
                                            <span class="badge badge-safe">Lunas</span>
                                        @else
                                            <span class="badge badge-danger">Kasbon</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" style="text-align: center;">Belum ada transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 2. Tabel Top 5 Produk --}}
            <div class="table-card" style="height: 350px;">
                <div style="padding: 16px; border-bottom: 1px solid #e2e8f0; font-weight: 800; font-size: 14px; color: #0284c7;">
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
                                    <td style="font-weight: 700;">{{ $tp->nama_produk }}</td>
                                    <td style="font-weight: 800; color: #0284c7;">{{ (int)$tp->total_qty }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" style="text-align: center;">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- 3. Tabel Stok Menipis --}}
            <div class="table-card" style="height: 350px;">
                <div style="padding: 16px; border-bottom: 1px solid #e2e8f0; font-weight: 800; font-size: 14px; color: #dc2626;">
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
                                    <td style="font-weight: 700;">{{ $prd->nama_produk }}</td>
                                    <td style="color: #dc2626; font-weight: 800;">{{ $prd->stok_sekarang }} {{ $prd->satuan }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" style="text-align: center;">Semua stok aman.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // --- 1. Javascript Filter Cepat ---
    function setTanggal(tipe) {
        const today = new Date();
        let start = new Date();
        
        if (tipe === 'minggu') {
            start.setDate(today.getDate() - today.getDay()); // Mulai hari Minggu
        } else if (tipe === 'bulan') {
            start = new Date(today.getFullYear(), today.getMonth(), 1); // Tanggal 1 bulan ini
        }
        
        // Format ke YYYY-MM-DD
        const formatDate = (date) => {
            const d = new Date(date);
            let month = '' + (d.getMonth() + 1);
            let day = '' + d.getDate();
            const year = d.getFullYear();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            return [year, month, day].join('-');
        };

        document.getElementById('startDate').value = formatDate(start);
        document.getElementById('endDate').value = formatDate(today);
        document.getElementById('formFilter').submit();
    }

    // --- 2. Grafik Tren Omzet ---
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
                    size: '65%', // Perbesar lubang sedikit (dari 65%)
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