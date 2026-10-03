<aside class="sidebar">
    <div>
        <div class="profile-container">
            <div class="avatar-circle"></div>
        </div>

        <nav class="nav-menu">
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
            <a href="#" class="nav-item">
                <i class="fa-solid fa-cart-shopping"></i> Kasir
            </a>
            <a href="#" class="nav-item">
                <i class="fa-solid fa-book"></i> Buku Kasbon
            </a>
            <a href="{{ route('produk.index') }}"
                class="nav-item {{ request()->routeIs('produk.*') ? 'active' : '' }}">
                <i class="fa-solid fa-box-open"></i> Barang & Stok
            </a>
            <a href="#" class="nav-item">
                <i class="fa-solid fa-truck-fast"></i> Pasokan Supplier
            </a>
            <a href="{{ route('pengeluaran.index') }}"
                class="nav-item {{ request()->routeIs('pengeluaran.*') ? 'active' : '' }}">
                <i class="fa-solid fa-wallet"></i> Pengeluaran
            </a>
            <a href="#" class="nav-item">
                <i class="fa-solid fa-chart-pie"></i> Laporan & Omzet
            </a>
        </nav>
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
    </form>
</aside>