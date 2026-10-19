<aside class="sidebar">
    <div>
        <div class="logo-container">
            <img src="{{ asset('img/logo-laris.png') }}" alt="LARIS Logo">
        </div>

        <nav class="nav-menu">
            {{-- 1. Laporan & Omzet (Hanya Admin) --}}
            @if(auth()->user()->peran === 'admin')
                <a href="{{ route('laporan.index') }}" class="nav-item {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i> Laporan & Omzet
                </a>
            @endif

            {{-- 2. Kasir (Semua) --}}
            <a href="{{ route('kasir.index') }}" class="nav-item {{ request()->routeIs('kasir.*') ? 'active' : '' }}">
                <i class="fa-solid fa-cart-shopping"></i> Kasir
            </a>

            {{-- 3. Buku Kasbon (Semua) --}}
            <a href="{{ route('kasbon.index') }}" class="nav-item {{ request()->routeIs('kasbon.*') ? 'active' : '' }}">
                <i class="fa-solid fa-book"></i> Buku Kasbon
            </a>

            {{-- 4. Barang & Stok (Semua) --}}
            <a href="{{ route('produk.index') }}" class="nav-item {{ request()->routeIs('produk.*') || request()->routeIs('kategori.*') ? 'active' : '' }}">
                <i class="fa-solid fa-box-open"></i> Barang & Stok
            </a>

            {{-- 5. Pasokan Supplier (Hanya Admin) --}}
            @if(auth()->user()->peran === 'admin')
                <a href="{{ route('supplier.index') }}" class="nav-item {{ request()->routeIs('supplier.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-truck-fast"></i> Pasokan Supplier
                </a>
            @endif

            {{-- 6. Pengeluaran (Hanya Admin) --}}
            @if(auth()->user()->peran === 'admin')
                <a href="{{ route('pengeluaran.index') }}" class="nav-item {{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-wallet"></i> Pengeluaran
                </a>
            @endif
        </nav>
    </div>

    {{-- Tombol Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
    </form>
</aside>