{{-- FILTER BAR PERIODE LAPORAN (Dipakai Bersama Laporan Admin & Laporan Kasir) --}}
<div class="filter-bar">
    <div class="filter-bar-title">
        <i class="fa-solid fa-calendar-days"></i> {{ $filterTitle ?? 'Periode Laporan' }}
    </div>

    <form method="GET" action="{{ $action }}" id="formFilter" class="quick-filter">
        <button type="button" class="qf-btn" onclick="setTanggal('hari')">Hari Ini</button>
        <button type="button" class="qf-btn" onclick="setTanggal('minggu')">Minggu Ini</button>
        <button type="button" class="qf-btn" onclick="setTanggal('bulan')">Bulan Ini</button>

        <input type="date" name="start_date" id="startDate" value="{{ $startDate }}" class="search-input filter-date-input start">
        <span class="filter-date-sep">s/d</span>
        <input type="date" name="end_date" id="endDate" value="{{ $endDate }}" class="search-input filter-date-input">
        <button type="submit" class="btn-primary filter-submit-btn">Terapkan</button>
    </form>

    @if(!empty($showExports))
        <div class="filter-export-group">
            <a href="{{ route('laporan.exportExcel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-secondary btn-export-excel">
                <i class="fa-solid fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('laporan.exportPdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-secondary btn-export-pdf">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
        </div>
    @endif
</div>

@pushOnce('scripts')
<script>
    function setTanggal(tipe) {
        const today = new Date();
        let start = new Date();

        if (tipe === 'minggu') {
            start.setDate(today.getDate() - today.getDay());
        } else if (tipe === 'bulan') {
            start = new Date(today.getFullYear(), today.getMonth(), 1);
        }

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
</script>
@endPushOnce
