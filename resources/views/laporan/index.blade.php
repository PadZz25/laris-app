@extends('layouts.app')

@section('title', 'Laporan & Omzet')
@section('page-title', 'Laporan & Omzet')

@section('content')
<div class="page-scroll">
    <div class="laporan-wrapper">

        {{-- FILTER BAR & EXPORT --}}
        <div class="filter-bar">
            <div class="filter-bar-title">
                <i class="fa-solid fa-calendar-days"></i> Periode Laporan
            </div>
            
            <form method="GET" action="{{ route('laporan.index') }}" id="formFilter" class="quick-filter">
                <button type="button" class="qf-btn" onclick="setTanggal('hari')">Hari Ini</button>
                <button type="button" class="qf-btn" onclick="setTanggal('minggu')">Minggu Ini</button>
                <button type="button" class="qf-btn" onclick="setTanggal('bulan')">Bulan Ini</button>
                
                <input type="date" name="start_date" id="startDate" value="{{ $startDate }}" class="search-input filter-date-input start">
                <span class="filter-date-sep">s/d</span>
                <input type="date" name="end_date" id="endDate" value="{{ $endDate }}" class="search-input filter-date-input">
                <button type="submit" class="btn-primary filter-submit-btn">Terapkan</button>
            </form>

            <div class="filter-export-group">
                <a href="{{ route('laporan.exportExcel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-secondary btn-export-excel">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('laporan.exportPdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-secondary btn-export-pdf">
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

{{-- MODAL: DETAIL TRANSAKSI (LAPORAN) --}}
<div class="modal-overlay" id="modalDetailTransaksi">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fa-solid fa-receipt"></i> Detail Transaksi</h3>
            <button type="button" class="btn-close-modal" onclick="closeModalDetail()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <div class="modal-body">
            {{-- Info Header --}}
            <div class="laporan-detail-header">
                <div class="laporan-detail-top">
                    <div class="laporan-detail-nota" id="detailNota">-</div>
                    <div class="laporan-detail-date" id="detailTanggal">-</div>
                </div>
                <div class="laporan-detail-meta">
                    <div>Kasir: <strong id="detailKasir">-</strong></div>
                    <div>Pelanggan: <strong id="detailPelanggan">-</strong></div>
                    <div>Metode: <strong id="detailMetode" class="uppercase">-</strong></div>
                    <div>Status: <strong id="detailStatus">-</strong></div>
                </div>
            </div>

            {{-- Tabel Item --}}
            <table class="data-table laporan-detail-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody id="detailItemsTbody">
                    {{-- Diisi JS --}}
                </tbody>
            </table>

            {{-- Ringkasan Subtotal, Diskon, Total --}}
            <div class="laporan-detail-summary">
                <div class="laporan-detail-summary-row">
                    <span>Subtotal Belanja</span>
                    <strong id="detailSubtotal">Rp 0</strong>
                </div>
                <div class="laporan-detail-summary-row">
                    <span>Diskon</span>
                    <strong id="detailDiskon" class="discount">Rp 0</strong>
                </div>
            </div>

            {{-- Total --}}
            <div class="laporan-detail-total-box">
                <span class="laporan-detail-total-label">TOTAL AKHIR</span>
                <span class="laporan-detail-total-val" id="detailTotal">Rp 0</span>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeModalDetail()">Tutup</button>
            <a href="#" id="btnCetakUlang" target="_blank" class="btn-primary">
                <i class="fa-solid fa-print"></i> Cetak Ulang Struk
            </a>
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

    // --- 5. MODAL DETAIL TRANSAKSI ---
    async function showDetailTransaksi(id) {
        // Tampilkan modal kosong / loading
        document.getElementById('detailNota').textContent = 'Memuat...';
        document.getElementById('detailItemsTbody').innerHTML = '<tr><td colspan="3" class="laporan-empty-cell"><i class="fa-solid fa-spinner fa-spin"></i></td></tr>';
        document.getElementById('modalDetailTransaksi').classList.add('active');

        try {
            // Ambil data via AJAX
            const res = await fetch(`/laporan/transaksi/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            // Isi Header Modal
            document.getElementById('detailNota').textContent = data.nomor_nota;
            document.getElementById('detailTanggal').textContent = data.tanggal;
            document.getElementById('detailKasir').textContent = data.kasir;
            document.getElementById('detailPelanggan').textContent = data.pelanggan;
            document.getElementById('detailMetode').textContent = data.metode_bayar;
            document.getElementById('detailStatus').textContent = data.status_bayar === 'lunas' ? 'LUNAS' : 'BELUM LUNAS';
            document.getElementById('detailSubtotal').textContent = 'Rp ' + Number(data.total_belanja ?? data.total_akhir).toLocaleString('id-ID');
            document.getElementById('detailDiskon').textContent = 'Rp ' + Number(data.diskon ?? 0).toLocaleString('id-ID');
            document.getElementById('detailTotal').textContent = 'Rp ' + Number(data.total_akhir).toLocaleString('id-ID');

            // Isi Tabel Item
            const tbody = document.getElementById('detailItemsTbody');
            tbody.innerHTML = '';
            data.items.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td class="laporan-cell-bold">${escapeHtml(item.nama_produk)}</td>
                        <td style="text-align: center;">${Number(item.jumlah)} ${escapeHtml(item.satuan)}</td>
                        <td style="text-align: right;">Rp ${Number(item.subtotal).toLocaleString('id-ID')}</td>
                    </tr>
                `;
            });

            // Set Link Cetak Ulang
            document.getElementById('btnCetakUlang').href = `/kasir/nota/${data.id_penjualan}`;

        } catch (err) {
            console.error(err);
            showToast('Gagal memuat detail transaksi.', 'error');
            closeModalDetail();
        }
    }

    function closeModalDetail() {
        document.getElementById('modalDetailTransaksi').classList.remove('active');
    }

    // Tutup modal via klik overlay
    document.getElementById('modalDetailTransaksi').addEventListener('click', function (e) {
        if (e.target === this) closeModalDetail();
    });
</script>
@endpush
