{{-- MODAL: DETAIL TRANSAKSI & CETAK ULANG STRUK --}}
@php
    $detailUrlPrefix = rtrim($detailUrlPrefix ?? url('/laporan/transaksi'), '/');
@endphp

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

@pushOnce('scripts')
<script>
    const DETAIL_TRANSAKSI_URL_PREFIX = @json($detailUrlPrefix, JSON_UNESCAPED_SLASHES);

    async function showDetailTransaksi(id) {
        document.getElementById('detailNota').textContent = 'Memuat...';
        document.getElementById('detailItemsTbody').innerHTML = '<tr><td colspan="3" class="laporan-empty-cell"><i class="fa-solid fa-spinner fa-spin"></i></td></tr>';
        document.getElementById('modalDetailTransaksi').classList.add('active');

        try {
            const res = await fetch(`${DETAIL_TRANSAKSI_URL_PREFIX}/${encodeURIComponent(id)}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            document.getElementById('detailNota').textContent = data.nomor_nota;
            document.getElementById('detailTanggal').textContent = data.tanggal;
            document.getElementById('detailKasir').textContent = data.kasir;
            document.getElementById('detailPelanggan').textContent = data.pelanggan;
            document.getElementById('detailMetode').textContent = data.metode_bayar;
            document.getElementById('detailStatus').textContent = data.status_bayar === 'lunas' ? 'LUNAS' : 'BELUM LUNAS';
            document.getElementById('detailSubtotal').textContent = 'Rp ' + Number(data.total_belanja ?? data.total_akhir).toLocaleString('id-ID');
            document.getElementById('detailDiskon').textContent = 'Rp ' + Number(data.diskon ?? 0).toLocaleString('id-ID');
            document.getElementById('detailTotal').textContent = 'Rp ' + Number(data.total_akhir).toLocaleString('id-ID');

            const tbody = document.getElementById('detailItemsTbody');
            const rows = data.items.map(item => {
                const tr = document.createElement('tr');

                const tdName = document.createElement('td');
                tdName.className = 'laporan-cell-bold';
                tdName.textContent = item.nama_produk ?? '';

                const tdQty = document.createElement('td');
                tdQty.style.textAlign = 'center';
                tdQty.textContent = `${Number(item.jumlah)} ${item.satuan ?? ''}`;

                const tdSubtotal = document.createElement('td');
                tdSubtotal.style.textAlign = 'right';
                tdSubtotal.textContent = `Rp ${Number(item.subtotal).toLocaleString('id-ID')}`;

                tr.replaceChildren(tdName, tdQty, tdSubtotal);
                return tr;
            });
            tbody.replaceChildren(...rows);

            document.getElementById('btnCetakUlang').href = `/kasir/nota/${encodeURIComponent(data.id_penjualan)}`;
        } catch (err) {
            console.error(err);
            showToast('Gagal memuat detail transaksi.', 'error');
            closeModalDetail();
        }
    }

    function closeModalDetail() {
        document.getElementById('modalDetailTransaksi').classList.remove('active');
    }

    document.getElementById('modalDetailTransaksi').addEventListener('click', function (e) {
        if (e.target === this) closeModalDetail();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModalDetail();
    });
</script>
@endPushOnce
