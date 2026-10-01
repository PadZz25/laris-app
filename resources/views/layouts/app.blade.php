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
    </style>

    @stack('styles')
</head>
<body>

    @include('partials.sidebar')

    <div class="main-container">
        @include('partials.header', ['title' => trim($__env->yieldContent('page-title', 'Dashboard'))])

        @if (session('success'))
            <div class="flash-message flash-success">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>