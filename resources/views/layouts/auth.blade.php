<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Đăng nhập') - Licuti CMS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex min-vh-100 align-items-center justify-content-center overflow-hidden py-5 px-3" data-bs-theme="dark" style="background-color: #020617;">
    <!-- Background Glowing Orbs -->
    <div class="glow-orb" style="top:-10rem;left:-10rem;width:24rem;height:24rem;background:rgba(37,99,235,0.3);"></div>
    <div class="glow-orb" style="bottom:-10rem;right:-10rem;width:24rem;height:24rem;background:rgba(6,182,212,0.3);animation-delay:2s;"></div>
    <div class="position-absolute top-50 start-50 translate-middle w-100 h-100 dot-pattern opacity-25" style="max-width:80rem;pointer-events:none;"></div>

    <div class="position-relative w-100 z-1" style="max-width:28rem;">
        <!-- Logo & Title -->
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center rounded-3 mb-3 logo-icon-box" style="width:4rem;height:4rem;">
                <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <h1 class="fs-2 fw-bold text-gradient-primary">Licuti CMS</h1>
            <p class="mt-2 text-body-secondary small">Hệ thống Quản trị E-Commerce Hiện đại</p>
        </div>

        <!-- Content Card -->
        <div class="rounded-3 p-4 shadow-lg border" style="background:rgba(15,23,42,0.8);backdrop-filter:blur(24px);border-color:rgba(30,41,59,0.8) !important;">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="mt-4 text-center text-body-secondary" style="font-size:0.75rem;">
            &copy; {{ date('Y') }} Licuti CMS. Powered by Laravel & Bootstrap 5.
        </div>
    </div>
    @stack('scripts')
</body>
</html>
