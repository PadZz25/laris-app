@props(['title' => 'Dashboard', 'namaToko' => null])
@php
    $namaTokoDisplay = $namaToko ?? (\App\Models\Pengaturan::current()->nama_toko ?? 'Toko Ina');
@endphp

<header class="top-header">
    <div class="header-title">
        <h1>{!! $title !!}</h1>
        <span>{{ $namaTokoDisplay }}</span>
    </div>

    <div class="header-widgets">
        <div class="time-pill">
            <div>Waktu</div>
            <div class="time-val" id="live-clock">--:--:-- WIB</div>
            <div class="time-date" id="live-date">--</div>
        </div>

        <div class="user-pill">
            <div class="user-avatar-mini"><i class="fa-solid fa-user"></i></div>
            <div class="user-pill-info">
                <div class="user-pill-role">{{ ucfirst(auth()->user()->peran) }}</div>
                <div class="user-pill-name">{{ auth()->user()->nama_karyawan }}</div>
            </div>
        </div>
    </div>
</header>

@once
@push('scripts')
<script>
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', { hour12: false }).replace(/:/g, '.');
        const dateStr = now.toLocaleDateString('id-ID', {
            weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric'
        });
        document.getElementById('live-clock').textContent = timeStr + ' WIB';
        document.getElementById('live-date').textContent = dateStr;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
@endpush
@endonce