<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - LARIS Toko Ina</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/laris.css') }}?v=9">
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