@props(['title' => 'Dashboard'])

<header class="top-header">
    <div class="header-title">
        <h1>{{ $title }}</h1>
        <span>Toko Ina</span>
    </div>

    <div class="header-widgets">
        <div class="icon-btn"><i class="fa-regular fa-bell"></i></div>

        <div class="time-pill">
            <div>Waktu</div>
            <div class="time-val" id="live-clock">--:--:-- WIB</div>
            <div style="font-size: 8px; color: #64748b;" id="live-date">--</div>
        </div>

        <div class="user-pill">
            <div class="user-avatar-mini"><i class="fa-solid fa-user"></i></div>
            <div>
                <div style="font-size: 8px; color: #64748b;">{{ ucfirst(auth()->user()->peran) }}</div>
                <div style="font-weight: 800; font-size: 10px; color: #0f172a;">
                    {{ auth()->user()->nama_karyawan }}
                </div>
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