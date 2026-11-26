@if(!empty($themeSettings['show_announcement']) && !empty($themeSettings['announcement_text']))
    <div class="site-announcement">
        {{ $themeSettings['announcement_text'] }}
    </div>
@endif
<header class="site-header {{ !empty($themeSettings['header_sticky']) ? 'site-header--sticky' : '' }}" @if(!empty($themeSettings['header_bg'])) style="background-color: var(--header-bg);" @endif>
    <div class="site-shell site-header__inner">
        <a class="brand" href="{{ route('storefront.posts.index') }}">
            <span class="brand__mark">L</span>
            <span>{{ setting('brand_label', 'Licuti Journal') }}</span>
        </a>
        <nav class="site-nav" aria-label="Điều hướng chính">
            <a href="{{ route('storefront.posts.index') }}">Bài viết</a>
            <a href="{{ url('/admin/login') }}">Quản trị</a>
        </nav>
    </div>
</header>
