<header class="site-header">
    <div class="site-shell site-header__inner">
        <a class="brand" href="{{ route('storefront.posts.index') }}">
            <span class="brand__mark">L</span>
            <span>{{ $themeSettings['brand_label'] ?? 'Licuti Journal' }}</span>
        </a>
        <nav class="site-nav" aria-label="Điều hướng chính">
            <a href="{{ route('storefront.posts.index') }}">Bài viết</a>
            <a href="{{ url('/admin/login') }}">Quản trị</a>
        </nav>
    </div>
</header>
