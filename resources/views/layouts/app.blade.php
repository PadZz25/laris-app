<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - LARIS Toko Ina</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background-color: #cbd5e1;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ================= SIDEBAR ================= */
        .sidebar {
            width: 210px;
            background: linear-gradient(180deg, #162F32 0%, #429198 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 24px 14px;
            color: #ffffff;
            flex-shrink: 0;
        }

        .profile-container { display: flex; justify-content: center; margin-bottom: 20px; }

        .avatar-circle {
            width: 75px; height: 75px; border-radius: 50%;
            background-color: #e2e8f0;
        }

        .nav-menu { display: flex; flex-direction: column; gap: 12px; margin-top: 10px; }

        .nav-item {
            display: flex; align-items: center; gap: 14px;
            padding: 11px 16px; border-radius: 12px;
            color: #ffffff; font-size: 13px; font-weight: 700;
            cursor: pointer; text-decoration: none;
            transition: all 0.2s;
        }

        .nav-item.active {
            background-color: #e2e8f0; color: #0f172a;
            font-weight: 800; box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .nav-item:hover:not(.active) {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .logout-btn {
            width: 100%;
            padding: 11px 16px;
            border-radius: 12px;
            background-color: #dc2626;               /* merah solid */
            color: #ffffff;                          /* teks putih */
            font-size: 13px;
            font-weight: 800;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(220, 38, 38, 0.35);
        }

        .logout-btn:hover { 
            background-color: #b91c1c;               /* merah lebih gelap saat hover */
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(220, 38, 38, 0.45); 
        }

        .logout-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);
        }   

        /* ================= MAIN CONTAINER ================= */
        .main-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 16px;
            background-color: #cbd5e1;
            overflow: hidden;         /* ← ubah dari auto ke hidden */
            min-height: 0;
        }

        /* Wrapper untuk halaman yang butuh scroll bebas (dashboard, laporan) */
        .page-scroll {
            flex: 1;
            overflow-y: auto;
            min-height: 0;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* ═══ TABLE CARD (full-height, scroll di dalam) ═══ */
        .table-card {
            flex: 1;
            min-height: 0;
            background-color: #ffffff;
            border-radius: 16px;
            padding: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .table-card-scroll {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overflow-x: auto;
        }

        .table-card-footer {
            padding: 10px 16px;
            border-top: 1px solid #f1f5f9;
            font-size: 11px;
            color: #64748b;
            text-align: right;
            background-color: #ffffff;
            flex-shrink: 0;
        }

        .table-card-footer strong {
            color: #0f172a;
            font-weight: 800;
        }

        /* Header/stats/control tidak boleh mengecil */
        .stats-grid,
        .control-card {
            flex-shrink: 0;
        }

        /* TOP HEADER */
        .top-header { display: flex; justify-content: space-between; align-items: flex-start; }

        .header-title h1 {
            font-size: 22px; font-weight: 800; color: #0f172a;
            line-height: 1.1; letter-spacing: -0.3px;
        }

        .header-title span { font-size: 18px; color: #64748b; font-weight: 400; }

        .header-widgets { display: flex; align-items: center; gap: 10px; }

        .icon-btn {
            background-color: #ffffff; width: 40px; height: 40px;
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            border: 1px solid #e2e8f0; color: #0f172a; font-size: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .time-pill {
            background-color: #ffffff; padding: 6px 16px;
            border-radius: 12px; text-align: center;
            font-size: 9px; color: #64748b; border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .time-pill .time-val { font-size: 11px; font-weight: 800; color: #0f172a; margin: 1px 0; }

        .user-pill {
            background-color: #ffffff; padding: 6px 14px;
            border-radius: 12px; display: flex; align-items: center; gap: 10px;
            font-size: 9px; color: #64748b; border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .user-avatar-mini {
            width: 26px; height: 26px; background-color: #e2e8f0;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            color: #64748b; font-size: 12px;
        }

        /* FLASH MESSAGE */
        .flash-message {
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .flash-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .flash-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

        @yield('styles')

        /* ═══════════════════════════════════════════
        KOMPONEN UMUM (dipakai di banyak halaman)
        ═══════════════════════════════════════════ */

        /* Card putih standar */
        .card {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        }

        /* Stats Grid (4 kartu ringkasan) */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .stat-card {
            background-color: #ffffff;
            border-radius: 14px;
            padding: 14px 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .stat-info .stat-label {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .stat-info .stat-value {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .stat-icon.blue   { background-color: #e0f2fe; color: #0284c7; }
        .stat-icon.yellow { background-color: #fef9c3; color: #ca8a04; }
        .stat-icon.red    { background-color: #fee2e2; color: #dc2626; }
        .stat-icon.green  { background-color: #dcfce7; color: #16a34a; }

        /* Control Card (search + filter + tombol) */
        .control-card {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 14px 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .control-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 300px;
            flex-wrap: wrap;
        }

        .search-wrapper {
            position: relative;
            width: 280px;
            max-width: 100%;
        }

        .search-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 13px;
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            padding: 9px 14px 9px 38px;
            border-radius: 30px;
            border: 1px solid #cbd5e1;
            outline: none;
            font-size: 12px;
            color: #0f172a;
            transition: all 0.2s;
        }

        .search-input:focus {
            border-color: #429198;
            box-shadow: 0 0 0 3px rgba(66, 145, 152, 0.15);
        }

        .category-group {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding-bottom: 2px;
        }

        .cat-pill {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #334155;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s;
        }

        .cat-pill:hover { background-color: #f1f5f9; }

        .cat-pill.active {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 800;
            border-color: #0f172a;
        }

        /* Button Primary */
        .btn-primary {
            background: linear-gradient(135deg, #162F32 0%, #429198 100%);
            color: #ffffff;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            box-shadow: 0 4px 10px rgba(66, 145, 152, 0.25);
            transition: transform 0.15s, box-shadow 0.15s;
            text-decoration: none;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(66, 145, 152, 0.35);
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: left;
        }

        .data-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 800;
            padding: 12px 14px;
            border-bottom: 2px solid #e2e8f0;
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .data-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #0f172a;
            vertical-align: middle;
        }

        .data-table tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Badge */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .badge-safe    { background-color: #dcfce7; color: #15803d; }
        .badge-warning { background-color: #fef9c3; color: #a16207; }
        .badge-danger  { background-color: #fee2e2; color: #b91c1c; }
        .badge-info    { background-color: #e0f2fe; color: #0284c7; }

        /* Badge Kode/SKU */
        .code-badge {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            background-color: #f1f5f9;
            padding: 3px 8px;
            border-radius: 6px;
            color: #475569;
            font-size: 11px;
        }

        /* Tombol Aksi Icon (Edit/Hapus) */
        .action-btns-cell {
            display: flex;
            gap: 6px;
            justify-content: center;
        }

        .btn-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #334155;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 11px;
            transition: all 0.15s;
            text-decoration: none;
        }

        .btn-icon:hover {
            background-color: #f1f5f9;
            border-color: #0f172a;
        }

        .btn-icon.danger:hover {
            background-color: #fee2e2;
            border-color: #dc2626;
            color: #dc2626;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
            font-size: 13px;
        }

        .empty-state i {
            font-size: 36px;
            margin-bottom: 12px;
            display: block;
            color: #cbd5e1;
        }

       /* ═══════════════════════════════════════════
        MODAL (reusable)
        ═══════════════════════════════════════════ */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            backdrop-filter: blur(2px);
            padding: 20px;
        }

        .modal-overlay.active { display: flex; }

        .modal-card {
            background: #ffffff;
            border-radius: 20px;
            width: 580px;
            max-width: 92%;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            animation: modalIn 0.2s ease;
        }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.95); }
            to   { opacity: 1; transform: scale(1); }
        }

        /* Header */
        .modal-header {
            padding: 18px 24px;
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .modal-header h3 {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }

        .btn-close-modal {
            background: none;
            border: none;
            font-size: 18px;
            color: #64748b;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            transition: background 0.15s, color 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-close-modal:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }

        /* Body (scrollable) */
        .modal-body {
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
        }

        .modal-body .form-group { margin-bottom: 0; }

        /* Footer */
        .modal-footer {
            padding: 14px 24px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-shrink: 0;
        }

        /* Form group & input (dalam modal) */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
        }

        .form-group label .required {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 9px 12px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            outline: none;
            font-size: 12px;
            color: #0f172a;
            background-color: #ffffff;
            font-family: inherit;
            transition: all 0.15s;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #429198;
            box-shadow: 0 0 0 3px rgba(66, 145, 152, 0.15);
        }

        .form-group input.error,
        .form-group select.error {
            border-color: #ef4444;
            background-color: #fef2f2;
        }

        .form-grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .field-error {
            font-size: 10px;
            color: #ef4444;
            font-weight: 600;
            margin-top: 2px;
        }

        /* Icon input wrapper */
        .input-icon-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-wrapper i {
            position: absolute;
            left: 12px;
            color: #64748b;
            font-size: 13px;
            pointer-events: none;
            z-index: 1;
        }

        .input-icon-wrapper input {
            padding-left: 36px !important;
        }

        /* Upload dropzone */
        .image-upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 20px 16px;
            background-color: #f8fafc;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            min-height: 140px;
            text-align: center;
        }

        .image-upload-box:hover {
            border-color: #429198;
            background-color: #f0fdfa;
        }

        .image-upload-box i {
            font-size: 26px;
            color: #429198;
        }

        .image-upload-box span {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
        }

        .image-upload-box small {
            font-size: 9.5px;
            color: #94a3b8;
            line-height: 1.4;
        }

        .image-upload-box img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
            object-fit: contain;
            display: block;
        }

        .file-input-hidden {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        /* Tombol Modal */
        .btn-cancel {
            background-color: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 9px 18px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: background 0.15s;
        }

        .btn-cancel:hover { background-color: #f1f5f9; }

        .btn-save {
            background-color: #0f172a;
            color: #ffffff;
            border: none;
            padding: 9px 20px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
            transition: background 0.15s;
        }

        .btn-save:hover { background-color: #162F32; }


        /* Modal ukuran kecil (konfirmasi hapus) */
        .modal-card-sm {
            width: 420px !important;
        }

        /* Tombol Danger (untuk hapus) */
        .btn-danger {
            background-color: #dc2626;
            color: #ffffff;
            border: none;
            padding: 9px 20px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
            box-shadow: 0 4px 10px rgba(220, 38, 38, 0.25);
            transition: background 0.15s, transform 0.15s;
        }

        .btn-danger:hover {
            background-color: #b91c1c;
            transform: translateY(-1px);
        }

        /* Info box kecil di dalam modal */
        .modal-info-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 11px;
            color: #475569;
            border-left: 3px solid #429198;
            line-height: 1.6;
        }

        .modal-info-box.warning {
            background: #fef2f2;
            border-left-color: #dc2626;
            color: #991b1b;
        }

        /* Foto existing di modal edit */
        .foto-existing {
            position: relative;
            display: inline-block;
            margin-top: 8px;
        }

        .foto-existing img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 10px;
            object-fit: contain;
            display: block;
            border: 2px solid #e2e8f0;
        }

        .foto-existing .badge-existing {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(15, 23, 42, 0.85);
            color: #ffffff;
            font-size: 9px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
        }


        /* ═══════════════════════════════════════════
        TOAST NOTIFICATION (pojok kanan atas)
        ═══════════════════════════════════════════ */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 2000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .toast {
            background: #ffffff;
            border-radius: 12px;
            padding: 14px 18px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 12px;
            font-weight: 600;
            min-width: 280px;
            max-width: 400px;
            border-left: 4px solid #22c55e;
            color: #0f172a;
            animation: toastIn 0.3s ease;
            pointer-events: auto;
        }

        .toast.error {
            border-left-color: #ef4444;
        }

        .toast i {
            font-size: 18px;
            flex-shrink: 0;
        }

        .toast.success i { color: #22c55e; }
        .toast.error i   { color: #ef4444; }

        @keyframes toastIn {
            from { opacity: 0; transform: translateX(100%); }
            to   { opacity: 1; transform: translateX(0); }
        }

        @keyframes toastOut {
            from { opacity: 1; transform: translateX(0); }
            to   { opacity: 0; transform: translateX(100%); }
        }

        /* ═══════════════════════════════════════════
        TABEL KATEGORI (dalam modal)
        ═══════════════════════════════════════════ */
        .kategori-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .kategori-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 800;
            padding: 10px 12px;
            border-bottom: 2px solid #e2e8f0;
            text-align: left;
            font-size: 11px;
        }

        .kategori-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #0f172a;
            vertical-align: middle;
        }

        .kategori-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .kategori-table-wrap {
            max-height: 320px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        /* Form inline tambah kategori */
        .kategori-add-form {
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
        }

        .kategori-add-form input {
            flex: 1;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            outline: none;
            font-size: 12px;
            font-family: inherit;
            transition: all 0.15s;
        }

        .kategori-add-form input:focus {
            border-color: #429198;
            box-shadow: 0 0 0 3px rgba(66, 145, 152, 0.15);
        }

        /* Sub-modal lebih tinggi dari modal biasa */
        #subModalEditKategori {
            z-index: 1100;
        }

        /* Modal kelola lebih ringkas paddingnya */
        #modalKelolaKategori .modal-body {
            padding: 18px 22px;
        }

        /* ═══════════════════════════════════════════
        STOK DISPLAY BOX (untuk modal opname)
        ═══════════════════════════════════════════ */
        .stok-display-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 14px 16px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .stok-display-box .label {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .stok-display-box .value {
            font-size: 22px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
            transition: color 0.2s;
        }

        .stok-display-box .value.positive { color: #16a34a; }
        .stok-display-box .value.negative { color: #dc2626; }
        .stok-display-box .value.neutral  { color: #64748b; font-size: 16px; }


        /* Tombol Secondary (abu, untuk aksi non-primary) */
        .btn-secondary {
            background: #e2e8f0;
            color: #334155;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
            transition: background 0.15s, transform 0.15s;
            white-space: nowrap;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
            transform: translateY(-1px);
        }

        .btn-secondary:active {
            transform: translateY(0);
        }

        /* ═══════════════════════════════════════════
        MODAL BESAR (untuk opname/picker)
        ═══════════════════════════════════════════ */
        .modal-card-lg {
            width: 960px !important;
            max-width: 95% !important;
            padding: 0 !important;
            overflow: hidden !important;
        }

        .modal-card-lg .modal-header {
            padding: 18px 24px;
            border-bottom: 1px solid #e2e8f0;
            margin: 0;
        }

        .modal-card-lg .modal-footer {
            padding: 14px 24px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            margin: 0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* ═══════════════════════════════════════════
        OPNAME LAYOUT (split view)
        ═══════════════════════════════════════════ */
        .opname-layout {
            display: flex;
            height: 62vh;
            min-height: 480px;
            max-height: 700px;
        }

        /* Panel Kiri */
        .opname-left {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #e2e8f0;
            background: #fafbfc;
        }

        .opname-search {
            position: relative;
            padding: 14px 14px 8px;
        }

        .opname-search i {
            position: absolute;
            left: 26px;
            top: 50%;
            transform: translateY(-30%);
            color: #64748b;
            font-size: 13px;
            pointer-events: none;
        }

        .opname-search input {
            width: 100%;
            padding: 9px 14px 9px 38px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            outline: none;
            font-size: 12px;
            font-family: inherit;
            background: #ffffff;
            transition: all 0.15s;
        }

        .opname-search input:focus {
            border-color: #429198;
            box-shadow: 0 0 0 3px rgba(66, 145, 152, 0.15);
        }

        .opname-cats {
            display: flex;
            gap: 6px;
            padding: 0 14px 10px;
            overflow-x: auto;
            flex-shrink: 0;
            scrollbar-width: thin;
        }

        .opname-cats::-webkit-scrollbar { height: 4px; }
        .opname-cats::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        .opname-cat {
            padding: 5px 12px;
            border-radius: 20px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #334155;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s;
        }

        .opname-cat:hover { background: #f1f5f9; }

        .opname-cat.active {
            background-color: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }

        .opname-list {
            flex: 1;
            overflow-y: auto;
            padding: 0 14px 14px;
            min-height: 0;
        }

        .opname-list::-webkit-scrollbar { width: 6px; }
        .opname-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        .opname-item {
            padding: 10px 12px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            transition: all 0.15s;
            margin-bottom: 6px;
        }

        .opname-item:hover {
            border-color: #429198;
            background: #f0fdfa;
        }

        .opname-item.selected {
            border-color: #429198;
            background: #f0fdfa;
            box-shadow: 0 0 0 2px rgba(66, 145, 152, 0.2);
        }

        .opname-item-nama {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.3;
        }

        .opname-item-meta {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
        }

        .opname-item-stok {
            font-size: 10px;
            font-weight: 800;
            color: #0f172a;
            background: #f1f5f9;
            padding: 4px 8px;
            border-radius: 6px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .opname-list-empty {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
            font-size: 11px;
        }

        .opname-list-empty i {
            font-size: 28px;
            margin-bottom: 8px;
            display: block;
            color: #cbd5e1;
        }

        /* Panel Kanan */
        .opname-right {
            flex: 1.2;
            min-width: 0;
            padding: 20px 22px;
            overflow-y: auto;
            background: #ffffff;
        }

        .opname-empty {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            text-align: center;
            gap: 10px;
        }

        .opname-empty i {
            font-size: 42px;
            color: #cbd5e1;
        }

        .opname-empty p {
            font-size: 12px;
            font-weight: 600;
        }

        .opname-detail-header {
            padding-bottom: 14px;
            border-bottom: 2px solid #f1f5f9;
            margin-bottom: 16px;
        }

        .opname-product-name {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.3;
        }

        .opname-product-meta {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        .opname-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 4px;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="toast-container" id="toastContainer"></div>
    @include('partials.sidebar')

    <div class="main-container">
        @include('partials.header', ['title' => trim($__env->yieldContent('page-title', 'Dashboard'))])

        @if (session('success'))
            <div class="flash-message flash-success">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="flash-message flash-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>