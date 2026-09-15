<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bảng điều khiển') - Licuti CMS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        (function(){var t=localStorage.getItem('theme');if(!t)t=window.matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light';document.documentElement.setAttribute('data-bs-theme',t);})();
    </script>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex min-vh-100">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <aside class="admin-sidebar custom-scrollbar" id="sidebar">
        <div class="d-flex align-items-center px-3 border-bottom" style="height:4rem;">
            <a href="/admin/dashboard" class="d-flex align-items-center gap-2 text-decoration-none fw-bold fs-5" style="color:var(--bs-primary);">
                <div class="logo-icon-box"><svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></div>
                <span>Licuti CMS</span>
            </a>
        </div>
        <nav class="flex-grow-1 px-3 py-3 overflow-y-auto custom-scrollbar">
            <div class="sidebar-section-label">Hệ thống chính</div>
            @foreach([['/admin/dashboard','admin/dashboard*','Bảng điều khiển','M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],['users','admin/users*','Người dùng','M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],['roles','admin/roles*','Vai trò & Quyền','M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],['media','admin/media*','Thư viện Media','M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z']] as $nav)
                @php $href=str_starts_with($nav[0],'/')?$nav[0]:route('admin.'.$nav[0].'.index'); @endphp
                <a href="{{$href}}" class="sidebar-link {{request()->is($nav[1])?'active':''}}"><svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{$nav[3]}}"/></svg><span>{{$nav[2]}}</span></a>
            @endforeach
            <div class="sidebar-section-label mt-3">Quản trị Nội dung</div>
            @foreach(['post-categories'=>'Danh mục Bài viết:M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10','tags'=>'Tags:M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z','posts'=>'Bài viết:M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z','pages'=>'Trang tĩnh:M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','banners'=>'Banners:M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z','menus'=>'Menu điều hướng:M4 6h16M4 12h16M4 18h16'] as $key=>$val)
                @php [$label,$icon]=explode(':',$val,2); @endphp
                <a href="{{route('admin.'.$key.'.index')}}" class="sidebar-link {{request()->is('admin/'.$key.'*')?'active':''}}"><svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{$icon}}"/></svg><span>{{$label}}</span></a>
            @endforeach
            <div class="sidebar-section-label mt-3">Quản lý Sản phẩm</div>
            @foreach(['categories'=>'Danh mục SP:M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z','brands'=>'Thương hiệu:M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z','product-attributes'=>'Thuộc tính:M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4','product-variants'=>'Biến thể:M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z','product-reviews'=>'Đánh giá:M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'] as $key=>$val)
                @php [$label,$icon]=explode(':',$val,2); @endphp
                <a href="{{route('admin.'.$key.'.index')}}" class="sidebar-link {{request()->is('admin/'.$key.'*')?'active':''}}"><svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{$icon}}"/></svg><span>{{$label}}</span></a>
            @endforeach
            <a href="{{route('admin.products.index')}}" class="sidebar-link {{request()->is('admin/products*')&&!request()->is('admin/product-*')?'active':''}}"><svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Sản phẩm</span></a>
            <div class="sidebar-section-label mt-3">Giao dịch & Đơn hàng</div>
            @foreach(['carts'=>'Giỏ hàng:M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z','orders'=>'Đơn hàng:M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01','payment-methods'=>'Cổng TT:M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z','payments'=>'Giao dịch:M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z','coupons'=>'Mã giảm giá:M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z','flash-sales'=>'Flash Sale:M13 10V3L4 14h7v7l9-11h-7z'] as $key=>$val)
                @php [$label,$icon]=explode(':',$val,2); @endphp
                <a href="{{route('admin.'.$key.'.index')}}" class="sidebar-link {{request()->is('admin/'.$key.'*')?'active':''}}"><svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{$icon}}"/></svg><span>{{$label}}</span></a>
            @endforeach
            <div class="sidebar-section-label mt-3">Kho hàng</div>
            @foreach(['warehouses'=>'Kho hàng:M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z','inventories'=>'Kiểm kê:M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'] as $key=>$val)
                @php [$label,$icon]=explode(':',$val,2); @endphp
                <a href="{{route('admin.'.$key.'.index')}}" class="sidebar-link {{request()->is('admin/'.$key.'*')?'active':''}}"><svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{$icon}}"/></svg><span>{{$label}}</span></a>
            @endforeach
            <div class="sidebar-section-label mt-3">Hệ thống</div>
            <a href="{{route('admin.languages.index')}}" class="sidebar-link {{request()->is('admin/languages*')?'active':''}}"><svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg><span>Ngôn ngữ</span></a>
            <a href="{{route('admin.settings.edit')}}" class="sidebar-link {{request()->is('admin/settings*')?'active':''}}"><svg class="sidebar-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Cấu hình</span></a>
        </nav>
    </aside>
    <div class="admin-main">
        <header class="admin-header">
            <button type="button" class="btn btn-link p-0 me-3 d-lg-none text-body-secondary" id="sidebarToggle">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex-grow-1"></div>
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-link p-1 text-body-secondary" id="themeToggle" title="Chuyển giao diện">
                    <svg id="themeIconLight" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="d-none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg id="themeIconDark" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>
                <div class="dropdown">
                    <button class="user-avatar-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <div class="user-avatar-circle">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</div>
                        <div class="d-none d-md-block text-start lh-1">
                            <span class="d-block fw-semibold" style="font-size:0.8125rem;">{{ auth()->user()->name ?? 'Admin' }}</span>
                            <span class="d-block text-body-secondary" style="font-size:0.6875rem;">{{ auth()->user()->email ?? '' }}</span>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="{{ route('admin.users.edit', auth()->user()->uuid ?? '#') }}">Hồ sơ cá nhân</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><button class="dropdown-item text-danger" type="button" id="btn-logout-header">Đăng xuất</button></li>
                    </ul>
                </div>
            </div>
        </header>
        <main class="admin-content">@yield('content')</main>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var themeToggle = document.getElementById('themeToggle');
        var lightIcon = document.getElementById('themeIconLight');
        var darkIcon = document.getElementById('themeIconDark');
        function updateThemeIcons() {
            var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            lightIcon.classList.toggle('d-none', !isDark);
            darkIcon.classList.toggle('d-none', isDark);
        }
        updateThemeIcons();
        themeToggle.addEventListener('click', function() {
            var cur = document.documentElement.getAttribute('data-bs-theme');
            var next = cur === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', next);
            localStorage.setItem('theme', next);
            updateThemeIcons();
        });
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        var sidebarToggle = document.getElementById('sidebarToggle');
        function openSidebar() { sidebar.classList.add('show'); overlay.classList.add('show'); }
        function closeSidebar() { sidebar.classList.remove('show'); overlay.classList.remove('show'); }
        if (sidebarToggle) sidebarToggle.addEventListener('click', openSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
        function handleLogout() {
            AdminUI.confirm({ title: 'Đăng xuất?', text: "Bạn có chắc chắn muốn thoát?", icon: 'question', confirmText: 'Đăng xuất', cancelText: 'Hủy' }, function() {
                document.getElementById('logout-form').submit();
            });
        }
        var btnLogoutHeader = document.getElementById('btn-logout-header');
        if (btnLogoutHeader) btnLogoutHeader.addEventListener('click', handleLogout);
    });
    </script>
    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display:none;">@csrf</form>
    @if(session('success'))<script>document.addEventListener('DOMContentLoaded',function(){if(window.AdminUI)window.AdminUI.notify('success',@json(session('success')));});</script>@endif
    @if(session('error'))<script>document.addEventListener('DOMContentLoaded',function(){if(window.AdminUI)window.AdminUI.notify('error',@json(session('error')));});</script>@endif
    @if(session('warning'))<script>document.addEventListener('DOMContentLoaded',function(){if(window.AdminUI)window.AdminUI.notify('warning',@json(session('warning')));});</script>@endif
    @if($errors->any())<script>document.addEventListener('DOMContentLoaded',function(){if(window.AdminUI)window.AdminUI.notify('error',@json($errors->first()));});</script>@endif
    <x-admin.media-picker />
    @stack('scripts')
</body>
</html>