@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')

    {{-- FILTER PERIODE TRANSAKSI --}}
    @include('partials.periode_filter_bar', [
        'action'      => route('kasir.laporan'),
        'startDate'   => $startDate,
        'endDate'     => $endDate,
        'filterTitle' => 'Periode Transaksi',
        'showExports' => false,
    ])

    {{-- KONTROL PENCARIAN & STATUS --}}
    <section class="control-card">
        <div class="control-left">
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text"
                       id="searchRiwayat"
                       class="search-input"
                       placeholder="Cari No. Nota, Pelanggan, atau Kasir..."
                       oninput="filterRiwayat()">
            </div>
            <select id="filterStatusRiwayat"
                    class="filter-select"
                    onchange="filterRiwayat()">
                <option value="">Semua Status</option>
                <option value="lunas">Lunas</option>
                <option value="belum_lunas">Kasbon</option>
            </select>
        </div>
        <div class="laporan-hint-text">
            <i class="fa-solid fa-circle-info"></i> Klik baris transaksi untuk melihat rincian &amp; cetak ulang struk
        </div>
    </section>

    {{-- TABEL RIWAYAT TRANSAKSI --}}
    <section class="table-card">
        <div class="table-card-header">
            <i class="fa-solid fa-clock-rotate-left"></i> History Transaksi
        </div>
        <div class="table-card-scroll">
            <table class="data-table" id="riwayatTable">
                <thead>
                    <tr>
                        <th>Nota &amp; Waktu</th>
                        <th>Kasir</th>
                        <th>Pelanggan</th>
                        <th>Metode Bayar</th>
                        <th style="text-align: right;">Total Akhir</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="riwayatTbody">
                    @forelse($riwayatTransaksi as $trx)
                        <tr class="laporan-trx-row"
                            data-search="{{ strtolower($trx->nomor_nota . ' ' . ($trx->pelanggan->nama_pelanggan ?? 'Umum') . ' ' . ($trx->karyawan->nama_karyawan ?? '')) }}"
                            data-status="{{ $trx->status_bayar }}"
                            onclick="showDetailTransaksi({{ $trx->id_penjualan }})"
                            title="Klik untuk melihat rincian">
                            <td>
                                <div class="laporan-nota-code">{{ $trx->nomor_nota }}</div>
                                <div class="laporan-nota-time">{{ \Carbon\Carbon::parse($trx->tanggal)->format('d M Y, H:i') }}</div>
                            </td>
                            <td>{{ $trx->karyawan->nama_karyawan ?? '-' }}</td>
                            <td>{{ $trx->pelanggan->nama_pelanggan ?? 'Umum' }}</td>
                            <td><span class="code-badge">{{ strtoupper($trx->metode_bayar) }}</span></td>
                            <td class="laporan-cell-total" style="text-align: right;">
                                Rp {{ number_format($trx->total_akhir, 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                @if($trx->status_bayar === 'lunas')
                                    <span class="badge badge-safe">Lunas</span>
                                @else
                                    <span class="badge badge-danger">Kasbon</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div class="action-btns-cell">
                                    <button type="button"
                                            class="btn-icon"
                                            title="Detail &amp; Cetak Ulang Struk"
                                            onclick="event.stopPropagation(); showDetailTransaksi({{ $trx->id_penjualan }})">
                                        <i class="fa-solid fa-receipt"></i>
                                    </button>
                                    <a href="{{ route('kasir.nota', $trx->id_penjualan) }}"
                                       target="_blank"
                                       class="btn-icon"
                                       title="Cetak Ulang Struk"
                                       onclick="event.stopPropagation()">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRiwayatRow">
                            <td colspan="7" class="laporan-empty-cell">Belum ada transaksi pada periode ini.</td>
                        </tr>
                    @endforelse
                    @if($riwayatTransaksi->isNotEmpty())
                        <tr id="noMatchRiwayatRow" style="display: none;">
                            <td colspan="7" class="laporan-empty-cell">Tidak ada transaksi yang cocok dengan pencarian.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="table-card-footer">
            Menampilkan <strong id="visibleRiwayatCount">{{ $riwayatTransaksi->count() }}</strong>
            dari <strong>{{ $riwayatTransaksi->count() }}</strong> transaksi
        </div>
    </section>

    @include('partials.detail_transaksi_modal', [
        'detailUrlPrefix' => url('/kasir/laporan/transaksi'),
    ])

@endsection

@push('scripts')
<script>
    function filterRiwayat() {
        const keyword = document.getElementById('searchRiwayat').value.toLowerCase().trim();
        const status = document.getElementById('filterStatusRiwayat').value;
        const rows = document.querySelectorAll('#riwayatTbody tr.laporan-trx-row');
        let visible = 0;

        rows.forEach(row => {
            const matchSearch = keyword === '' || (row.dataset.search || '').includes(keyword);
            const matchStatus = status === '' || row.dataset.status === status;

            if (matchSearch && matchStatus) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        const counter = document.getElementById('visibleRiwayatCount');
        if (counter) counter.textContent = visible;

        const noMatchRow = document.getElementById('noMatchRiwayatRow');
        if (noMatchRow) {
            noMatchRow.style.display = visible === 0 ? '' : 'none';
        }
    }
</script>
@endpush
