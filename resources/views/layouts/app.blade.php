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
            overflow-y: auto;
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