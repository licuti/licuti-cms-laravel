<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description ?? 'Tin tức và góc nhìn từ Licuti.' }}">
    <title>@yield('title', 'Tin tức') · {{ setting('site_name', 'Licuti') }}</title>
    <style>:root { --accent: {{ $themeSettings['accent_color'] ?? '#d9f05b' }}; }</style>
    <link rel="stylesheet" href="{{ theme_asset('css/theme.css') }}">
    <script src="{{ theme_asset('js/theme.js') }}" defer></script>
</head>
<body>
    @include('theme::partials.header')

    <main class="site-main">
        @yield('content')
    </main>

    @include('theme::partials.footer')
</body>
</html>
