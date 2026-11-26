<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description ?? 'Tin tức và góc nhìn từ Licuti.' }}">
    <title>@yield('title', 'Tin tức') · {{ setting('site_name', 'Licuti') }}</title>
    @php($safeCustomCss = preg_replace('/<\s*\/?\s*(style|script)[^>]*>/i', '', (string) ($themeSettings['custom_css'] ?? '')))
    <style>
        :root {
            --accent: {{ $themeSettings['accent_color'] ?? '#d9f05b' }};
            @if(!empty($themeSettings['header_bg'])) --header-bg: {{ $themeSettings['header_bg'] }}; @endif
            @if(!empty($themeSettings['footer_bg'])) --footer-bg: {{ $themeSettings['footer_bg'] }}; @endif
        }
        @if(trim($safeCustomCss) !== '')
            {!! $safeCustomCss !!}
        @endif
    </style>
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
