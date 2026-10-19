@extends('layouts.app')

@section('title', 'Kasir')
@section('page-title', 'Kasir')

@section('content')

    <div class="pos-layout">

        {{-- PANEL KIRI: PRODUK --}}
        <div class="pos-left">
            <div class="pos-filter-card">
                <div class="pos-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="posSearch"
                           placeholder="Cari nama barang / scan barcode..."
                           oninput="renderProduk()">
                </div>
                <div class="pos-cats" id="posCats">
                    <div class="cat-pill active" data-kat="Semua" onclick="setKategori('Semua', this)">Semua</div>
                    @foreach ($kategoriList as $kat)
                        <div class="cat-pill" data-kat="{{ $kat->nama_kategori }}" onclick="setKategori('{{ $kat->nama_kategori }}', this)">
                            {{ $kat->nama_kategori }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pos-grid" id="posGrid">
                {{-- Diisi oleh JavaScript --}}
            </div>
        </div>

        {{-- PANEL KANAN: KERANJANG --}}
        <div class="pos-right">
            <div class="pos-cart-header">Ringkasan Pembayaran</div>

            <div class="pos-cart-list" id="cartList">
                <div class="pos-cart-empty">
                    <i class="fa-solid fa-cart-flatbed"></i>
                    <div style="font-size: 12px; font-weight: 700;">Keranjang Kosong</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Pilih produk di sebelah kiri.</div>
                </div>
            </div>

            <div class="pos-summary">
                <div class="pos-summary-row">
                    <span>Subtotal</span>
                    <span id="sumSubtotal">Rp 0</span>
                </div>
                <div class="pos-summary-row">
                    <span>Diskon</span>
                    <span id="sumDiskon">Rp 0</span>
                </div>
                <div class="pos-summary-row total-row">
                    <span>Total</span>
                    <span id="sumTotal">Rp 0</span>
                </div>
            </div>

            <div class="pos-actions">
                <button class="btn-pay" id="btnPay" disabled onclick="openPaymentModal()">
                    <i class="fa-solid fa-cash-register"></i> Bayar & Cetak Nota
                </button>
                <button class="btn-clear-cart" onclick="clearCart()">
                    <i class="fa-solid fa-trash-can"></i> Hapus Keranjang
                </button>
            </div>
        </div>

    </div>

        {{-- MODAL: PEMBAYARAN POS (SPLIT VIEW) --}}
    <div class="modal-overlay" id="modalPembayaran">
        <div class="modal-card pos-modal-card">
            
            <div class="modal-header">
                <h3><i class="fa-solid fa-cash-register"></i> Konfirmasi Rincian Pembayaran</h3>
                <button type="button" class="btn-close-modal" onclick="closePaymentModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- body menggunakan flex kiri-kanan --}}
            <div class="pos-modal-body">
                
                {{-- SISI KIRI: Tabel Barang --}}
                <div class="pos-modal-left">
                    <div class="pos-modal-left-header">
                        <i class="fa-solid fa-list-ul" style="color: #429198;"></i> Rincian Barang
                    </div>
                    <div class="pos-modal-left-list">
                        <table class="pos-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th style="text-align: center;">Qty</th>
                                    <th style="text-align: right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="modalItemList">
                                <!-- Diisi oleh JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- SISI KANAN: Pembayaran --}}
                <div class="pos-modal-right">
                    
                    <div>
                        <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Pilih Metode Pembayaran:</div>
                        <div class="pos-metode-grid">
                            <div class="pos-metode-btn active" id="btnMetodeTunai" onclick="setMetode('Tunai')">
                                <i class="fa-solid fa-money-bill-wave" style="font-size: 20px; margin-bottom: 4px;"></i> Tunai
                            </div>
                            <div class="pos-metode-btn" id="btnMetodeQris" onclick="setMetode('QRIS')">
                                <i class="fa-solid fa-qrcode" style="font-size: 20px; margin-bottom: 4px;"></i> QRIS/Bank
                            </div>
                            <div class="pos-metode-btn" id="btnMetodeKasbon" onclick="setMetode('Kasbon')">
                                <i class="fa-solid fa-book" style="font-size: 20px; margin-bottom: 4px;"></i> Kasbon
                            </div>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px;">
                        {{-- Tunai --}}
                        <div id="sectionTunai">
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label>Uang Diterima (Rp)</label>
                                <input type="number" id="inputUangDiterima" placeholder="0" oninput="hitungKembalian()" style="font-size: 16px; font-weight: 800; padding: 10px;">
                            </div>
                            <div class="form-group">
                                <label>Uang Kembalian (Rp)</label>
                                <input type="text" id="inputKembalian" readonly style="font-size: 16px; font-weight: 800; background: #e2e8f0; color: #16a34a; border: none; padding: 10px;">
                            </div>
                        </div>

                        {{-- QRIS --}}
                        <div id="sectionQris" style="display: none; text-align: center; padding: 10px 0;">
                            <div style="font-size: 14px; font-weight: 800; color: #0f172a;"><i class="fa-solid fa-circle-check" style="color: #22c55e;"></i> Pembayaran Non-Tunai</div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Pastikan dana QRIS/Transfer sudah masuk ke rekening toko.</div>
                        </div>

                        {{-- Kasbon --}}
                        <div id="sectionKasbon" style="display: none;">
                            <div class="form-group">
                                <label>Pilih Warga (Buku Kasbon)</label>
                                <select id="selectPelanggan" style="font-size: 13px; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;">
                                    <option value="">-- Pilih Nama Warga --</option>
                                    @foreach ($pelangganList as $plg)
                                        <option value="{{ $plg->id_pelanggan }}">{{ $plg->nama_pelanggan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="pos-grandtotal-box" style="margin-top: auto; background: #0f172a; padding: 16px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 10px; color: #cbd5e1; letter-spacing: 0.5px;">TOTAL TAGIHAN:</div>
                            <div style="font-size: 26px; font-weight: 800; color: #ffffff;" id="modalTotalBayar">Rp 0</div>
                        </div>
                        <div id="labelMetode" style="background: rgba(255,255,255,0.1); color: #ffffff; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; border: 1px solid rgba(255,255,255,0.2);">
                            Tunai
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closePaymentModal()">Batal</button>
                <button type="button" class="btn-save" onclick="prosesPembayaran()" style="background: #0284c7;">
                    <i class="fa-solid fa-print"></i> Proses & Cetak Nota
                </button>
            </div>

        </div>
    </div>

        {{-- MODAL: PEMBAYARAN POS (SPLIT VIEW) --}}
    <div class="modal-overlay" id="modalPembayaran">
        <div class="modal-card pos-modal-card">
            
            {{-- Header --}}
            <div class="modal-header">
                <h3><i class="fa-solid fa-cash-register"></i> Konfirmasi Rincian Pembayaran</h3>
                <button type="button" class="btn-close-modal" onclick="closePaymentModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Body Split --}}
            <div class="pos-modal-body">
                
                {{-- KIRI: Daftar Barang --}}
                <div class="pos-modal-left">
                    <div class="pos-modal-left-header">
                        <i class="fa-solid fa-list-ul" style="color: #429198;"></i> Rincian Barang
                    </div>
                    <div class="pos-modal-left-list">
                        <table class="pos-table">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th style="text-align: center;">Qty</th>
                                    <th style="text-align: right;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="modalItemList">
                                {{-- Diisi JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- KANAN: Metode & Pembayaran --}}
                <div class="pos-modal-right">
                    
                    {{-- Pilihan Metode --}}
                    <div>
                        <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 10px;">Pilih Metode Pembayaran:</div>
                        <div class="pos-metode-grid">
                            <div class="pos-metode-btn active" id="btnMetodeTunai" onclick="setMetode('Tunai')">
                                <i class="fa-solid fa-money-bill-wave" style="font-size: 20px; margin-bottom: 4px;"></i>
                                Tunai
                            </div>
                            <div class="pos-metode-btn" id="btnMetodeQris" onclick="setMetode('QRIS')">
                                <i class="fa-solid fa-qrcode" style="font-size: 20px; margin-bottom: 4px;"></i>
                                QRIS/Bank
                            </div>
                            <div class="pos-metode-btn" id="btnMetodeKasbon" onclick="setMetode('Kasbon')">
                                <i class="fa-solid fa-book" style="font-size: 20px; margin-bottom: 4px;"></i>
                                Kasbon
                            </div>
                        </div>
                    </div>

                    {{-- Form Dinamis --}}
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px;">
                        
                        {{-- Tunai --}}
                        <div id="sectionTunai">
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label>Uang Diterima (Rp)</label>
                                <input type="number" id="inputUangDiterima" class="pos-input-lg" placeholder="0" oninput="hitungKembalian()">
                            </div>
                            <div class="form-group">
                                <label>Uang Kembalian (Rp)</label>
                                <input type="text" id="inputKembalian" class="pos-input-lg" readonly style="background: #e2e8f0; color: #16a34a; border: none;">
                            </div>
                        </div>

                        {{-- QRIS --}}
                        <div id="sectionQris" style="display: none; text-align: center; padding: 10px 0;">
                            <div style="font-size: 14px; font-weight: 800; color: #0f172a;"><i class="fa-solid fa-circle-check" style="color: #22c55e;"></i> Pembayaran Non-Tunai</div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 4px;">Pastikan dana QRIS/Transfer sudah masuk ke rekening toko.</div>
                        </div>

                        {{-- Kasbon --}}
                        <div id="sectionKasbon" style="display: none;">
                            <div class="form-group">
                                <label>Pilih Warga (Buku Kasbon)</label>
                                <select id="selectPelanggan" style="font-size: 13px; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1;">
                                    <option value="">-- Pilih Nama Warga --</option>
                                    @foreach ($pelangganList as $plg)
                                        <option value="{{ $plg->id_pelanggan }}">{{ $plg->nama_pelanggan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </div>

                    {{-- Total Tagihan --}}
                    <div class="pos-grandtotal-box" style="margin-top: auto;">
                        <div>
                            <div style="font-size: 10px; color: #cbd5e1; letter-spacing: 0.5px;">TOTAL TAGIHAN:</div>
                            <div style="font-size: 26px; font-weight: 800; color: #ffffff;" id="modalTotalBayar">Rp 0</div>
                        </div>
                        <div class="pos-modal-badge" id="labelMetode" style="background: rgba(255,255,255,0.1); color: #ffffff; border: 1px solid rgba(255,255,255,0.2);">
                            <i class="fa-solid fa-money-bill-wave"></i> Tunai
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closePaymentModal()">Batal</button>
                <button type="button" class="btn-save" onclick="prosesPembayaran()" style="background: #0284c7; box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3);">
                    <i class="fa-solid fa-print"></i> Proses & Cetak Nota
                </button>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
<script>

        // ═══ NOTIFIKASI TOAST ═══
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        const iconClass = type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation';
        toast.innerHTML = `<i class="fa-solid ${iconClass}"></i><span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'toastOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3200);
    }

    // ═══ KONFIRMASI CUSTOM ═══
    let confirmCallback = null;
    function showConfirm(title, message, callback) {
        document.getElementById('konfirmasiTitle').innerHTML = `<i class="fa-solid fa-triangle-exclamation" style="color: #dc2626;"></i> ${title}`;
        document.getElementById('konfirmasiMessage').textContent = message;
        document.getElementById('btnConfirmAction').className = 'btn-danger';
        confirmCallback = callback;
        document.getElementById('modalKonfirmasiCustom').classList.add('active');
    }
    function closeConfirm() {
        document.getElementById('modalKonfirmasiCustom').classList.remove('active');
        confirmCallback = null;
    }
    function executeConfirm() {
        if (confirmCallback) confirmCallback();
        closeConfirm();
    }
    // Data dari Controller
    const produkList = @json($produkData);
    
    let activeCategory = 'Semua';
    let cart = [];

        // ═══ LOCAL STORAGE (AUTO-SAVE CART) ═══
    function saveCart() {
        localStorage.setItem('laris_pos_cart', JSON.stringify(cart));
    }

    function loadCart() {
        const saved = localStorage.getItem('laris_pos_cart');
        if (!saved) return;

        try {
            const parsedCart = JSON.parse(saved);
            // Validasi ulang: pastikan produk masih ada & stok mencukupi
            cart = parsedCart.filter(item => {
                const produkTerbaru = produkList.find(p => p.id === item.id);
                if (!produkTerbaru) return false; // Produk dihapus dari DB

                // Update info produk (jika harga/stok berubah di tab lain)
                item.harga = produkTerbaru.harga;
                item.nama = produkTerbaru.nama;
                item.satuan = produkTerbaru.satuan;
                item.stok = produkTerbaru.stok;

                // Jika qty melebihi stok terbaru, potong
                if (item.qty > produkTerbaru.stok) {
                    item.qty = produkTerbaru.stok;
                }
                return item.qty > 0;
            });

            if (cart.length > 0) {
                showToast('Keranjang belanja dipulihkan', 'success');
            }
        } catch (e) {
            console.error('Gagal memuat keranjang', e);
            localStorage.removeItem('laris_pos_cart');
        }
    }

    // ═══ RENDER PRODUK ═══
    function setKategori(kat, el) {
        activeCategory = kat;
        document.querySelectorAll('#posCats .cat-pill').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        renderProduk();
    }

    function renderProduk() {
        const keyword = document.getElementById('posSearch').value.toLowerCase().trim();
        const grid = document.getElementById('posGrid');

        const filtered = produkList.filter(p => {
            const matchKat = activeCategory === 'Semua' || p.kategori === activeCategory;
            const haystack = `${p.nama} ${p.barcode || ''}`.toLowerCase();
            const matchSearch = keyword === '' || haystack.includes(keyword);
            return matchKat && matchSearch;
        });

        if (filtered.length === 0) {
            grid.innerHTML = `<div style="grid-column: 1/-1; text-align:center; padding:40px; color:#64748b;">Produk tidak ditemukan</div>`;
            return;
        }

        grid.innerHTML = filtered.map(p => {
            const imgHtml = p.foto 
                ? `<img src="${p.foto}" alt="${p.nama}">` 
                : `<i class="fa-solid fa-image"></i>`;

            return `
                <div class="pos-card" onclick="addToCart(${p.id})">
                    <div class="pos-card-img">
                        ${imgHtml}
                    </div>
                    <div class="pos-card-title">${p.nama}</div>
                    <div class="pos-card-price">Rp ${p.harga.toLocaleString('id-ID')}</div>
                    <div class="pos-card-stok">Stok: ${p.stok} ${p.satuan}</div>
                </div>
            `;
        }).join('');
    }

    // ═══ CART LOGIC ═══
        function addToCart(id) {
        const prod = produkList.find(p => p.id === id);
        if (!prod) return;

        const existing = cart.find(item => item.id === id);
        if (existing) {
            if (existing.qty < prod.stok) {
                existing.qty += 1;
            } else {
                // Ganti alert dengan Toast Error
                showToast(`Stok ${prod.nama} hanya tersisa ${prod.stok} ${prod.satuan}!`, 'error');
            }
        } else {
            cart.push({ id: prod.id, nama: prod.nama, harga: prod.harga, qty: 1, satuan: prod.satuan, stok: prod.stok });
        }
        saveCart();
        renderCart();
    }

    function changeQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if (!item) return;

        item.qty += delta;
        if (item.qty > item.stok) {
            item.qty = item.stok;
            // Ganti alert dengan Toast Error
            showToast(`Stok ${item.nama} terbatas!`, 'error');
        }
        if (item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
        saveCart();
        renderCart();
    }

    function clearCart() {
        if (cart.length === 0) return;
        
        // Ganti confirm() dengan Custom Modal
        showConfirm(
            'Kosongkan Keranjang?', 
            'Semua produk yang dipilih akan dihapus dari keranjang.', 
            () => {
                cart = [];
                saveCart();
                renderCart();
                showToast('Keranjang berhasil dikosongkan', 'success');
            }
        );
    }

    function renderCart() {
        const list = document.getElementById('cartList');
        const btnPay = document.getElementById('btnPay');

        if (cart.length === 0) {
            list.innerHTML = `
                <div class="pos-cart-empty">
                    <i class="fa-solid fa-cart-flatbed"></i>
                    <div style="font-size: 12px; font-weight: 700;">Keranjang Kosong</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Pilih produk di sebelah kiri.</div>
                </div>`;
            document.getElementById('sumSubtotal').textContent = 'Rp 0';
            document.getElementById('sumTotal').textContent = 'Rp 0';
            btnPay.disabled = true;
            return;
        }

        list.innerHTML = "";
        let subtotal = 0;

        cart.forEach(item => {
            const totalItem = item.harga * item.qty;
            subtotal += totalItem;

            list.innerHTML += `
                <div class="pos-cart-item">
                    <div class="pos-cart-item-info">
                        <div class="pos-cart-item-title">${item.nama}</div>
                        <div class="pos-cart-item-price">Rp ${item.harga.toLocaleString('id-ID')} x ${item.qty}</div>
                    </div>
                    <div class="pos-cart-controls">
                        <button class="btn-qty" onclick="changeQty(${item.id}, -1)">-</button>
                        <span class="qty-val">${item.qty}</span>
                        <button class="btn-qty" onclick="changeQty(${item.id}, 1)">+</button>
                    </div>
                </div>
            `;
        });

        document.getElementById('sumSubtotal').textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
        document.getElementById('sumTotal').textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
        btnPay.disabled = false;
    }

        // ═══ MODAL PEMBAYARAN (5.3) ═══
    let metodeTerpilih = 'Tunai';
    let totalBelanjaGlobal = 0;

    function openPaymentModal() {
        if (cart.length === 0) return;

        // Hitung total
        totalBelanjaGlobal = cart.reduce((sum, item) => sum + (item.harga * item.qty), 0);
        
        // Render Tabel Item di Modal
        let html = '';
        cart.forEach(item => {
            html += `
                <tr>
                    <td style="font-weight: 700;">${item.nama}</td>
                    <td style="text-align: center; font-weight: 800;">${item.qty}</td>
                    <td style="text-align: right; font-weight: 800; color: #0f172a;">Rp ${(item.harga * item.qty).toLocaleString('id-ID')}</td>
                </tr>
            `;
        });
        document.getElementById('modalItemList').innerHTML = html;
        document.getElementById('modalTotalBayar').textContent = `Rp ${totalBelanjaGlobal.toLocaleString('id-ID')}`;

        // Reset Form
        document.getElementById('inputUangDiterima').value = '';
        document.getElementById('inputKembalian').value = 'Rp 0';
        document.getElementById('selectPelanggan').value = '';
        setMetode('Tunai'); // Default ke Tunai

        // Tampilkan Modal
        document.getElementById('modalPembayaran').classList.add('active');
    }

    function closePaymentModal() {
        document.getElementById('modalPembayaran').classList.remove('active');
    }

    function setMetode(metode) {
        metodeTerpilih = metode;
        
        // Reset class aktif
        document.querySelectorAll('.pos-metode-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById('sectionTunai').style.display = 'none';
        document.getElementById('sectionQris').style.display = 'none';
        document.getElementById('sectionKasbon').style.display = 'none';

        // Set aktif
        if (metode === 'Tunai') {
            document.getElementById('btnMetodeTunai').classList.add('active');
            document.getElementById('sectionTunai').style.display = 'block';
            document.getElementById('labelMetode').innerHTML = '<i class="fa-solid fa-money-bill-wave"></i> Tunai';
        } else if (metode === 'QRIS') {
            document.getElementById('btnMetodeQris').classList.add('active');
            document.getElementById('sectionQris').style.display = 'block';
            document.getElementById('labelMetode').innerHTML = '<i class="fa-solid fa-qrcode"></i> QRIS / Transfer';
        } else if (metode === 'Kasbon') {
            document.getElementById('btnMetodeKasbon').classList.add('active');
            document.getElementById('sectionKasbon').style.display = 'block';
            document.getElementById('labelMetode').innerHTML = '<i class="fa-solid fa-book"></i> Kasbon Warga';
        }
    }

    function hitungKembalian() {
        const uangDiterima = parseFloat(document.getElementById('inputUangDiterima').value) || 0;
        const kembalian = uangDiterima - totalBelanjaGlobal;
        
        const inputKembalian = document.getElementById('inputKembalian');
        
        if (kembalian < 0) {
            inputKembalian.value = 'Uang Kurang!';
            inputKembalian.style.color = '#dc2626';
        } else {
            inputKembalian.value = `Rp ${kembalian.toLocaleString('id-ID')}`;
            inputKembalian.style.color = '#16a34a';
        }
    }

        async function prosesPembayaran() {
        // 1. Validasi Tunai
        let uangDiterima = 0;
        let kembalian = 0;

        if (metodeTerpilih === 'Tunai') {
            uangDiterima = parseFloat(document.getElementById('inputUangDiterima').value) || 0;
            if (uangDiterima < totalBelanjaGlobal) {
                showToast('Uang yang dibayarkan masih kurang!', 'error');
                return;
            }
            kembalian = uangDiterima - totalBelanjaGlobal;
        }
        
        // 2. Validasi Kasbon
        let idPelanggan = null;
        if (metodeTerpilih === 'Kasbon') {
            idPelanggan = document.getElementById('selectPelanggan').value;
            if (!idPelanggan) {
                showToast('Pilih nama warga untuk kasbon!', 'error');
                return;
            }
        }

        // 3. Siapkan Data
        const payload = {
            items: cart,
            metode_bayar: metodeTerpilih,
            id_pelanggan: idPelanggan,
            uang_diterima: uangDiterima,
            kembalian: kembalian
        };

        const btnProses = document.querySelector('#modalPembayaran .btn-save');
        btnProses.disabled = true;
        btnProses.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

        try {
            // 4. Kirim ke Backend
            const res = await fetch('{{ route("kasir.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (res.ok && data.success) {
                // 5. Sukses: Bersihkan Cart & LocalStorage
                cart = [];
                saveCart();
                renderCart();
                closePaymentModal();
                
                // Tampilkan Toast
                showToast('Transaksi Berhasil!', 'success');

                // Buka struk di tab baru untuk di-print
                window.open(`/kasir/nota/${data.id_penjualan}`, '_blank');
                
                // Refresh halaman setelah 2 detik untuk update stok di grid
                setTimeout(() => { location.reload(); }, 2000);

            } else {
                showToast(data.message || 'Gagal memproses transaksi.', 'error');
            }
        } catch (err) {
            console.error(err);
            showToast('Terjadi kesalahan jaringan.', 'error');
        } finally {
            btnProses.disabled = false;
            btnProses.innerHTML = '<i class="fa-solid fa-print"></i> Proses & Cetak Nota';
        }
    }

    // INIT
    loadCart();
    renderProduk();
    renderCart();
</script>
@endpush