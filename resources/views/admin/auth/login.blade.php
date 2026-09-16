@extends('layouts.auth')
@section('title', 'Đăng nhập Quản trị')

@section('content')
<div class="text-center mb-4">
    <h2 class="h4 fw-bold mb-1">Chào mừng trở lại!</h2>
    <p class="text-body-secondary small mb-0">Vui lòng đăng nhập bằng tài khoản quản trị viên</p>
</div>

@if($errors->any())
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 py-2" role="alert">
        <svg class="flex-shrink-0" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
        </svg>
        <div class="small">{{ $errors->first() }}</div>
    </div>
@endif

<form action="{{ route('admin.login.post') }}" method="POST">
    @csrf

    <x-admin.form-group label="Email Quản trị" name="email" required>
        <x-admin.input type="email" name="email" id="email" placeholder="admin@licuti.com" autocomplete="email" required value="{{ old('email', 'admin@licuti.com') }}" />
    </x-admin.form-group>

    <x-admin.form-group label="Mật khẩu" name="password" required>
        <div class="input-group">
            <x-admin.input type="password" name="password" id="password" placeholder="••••••••" autocomplete="current-password" required value="password" />
            <button type="button" id="toggle-password" class="btn btn-outline-secondary d-flex align-items-center" tabindex="-1" aria-label="Hiện mật khẩu" aria-pressed="false">
                <svg id="eye-icon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"></svg>
            </button>
        </div>
    </x-admin.form-group>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="form-check mb-0">
            <input type="checkbox" name="remember" id="remember" class="form-check-input" value="1">
            <label for="remember" class="form-check-label small">Ghi nhớ đăng nhập</label>
        </div>
        <a href="#" class="small text-decoration-none">Quên mật khẩu?</a>
    </div>

    <x-admin.button variant="primary" type="submit" size="lg" class="btn-auth-gradient w-100 active-scale">
        Đăng nhập hệ thống
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
        </svg>
    </x-admin.button>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dọn dẹp token cũ nếu có
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');

        // Toggle xem mật khẩu
        const $password = $('#password');
        const $toggle = $('#toggle-password');
        const $eye = $('#eye-icon');

        const eyeOpen = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>' +
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';

        const eyeClosed = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.957-.14 2.865-.41M7.5 4.21A10.5 10.5 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L18 18M3 3l18 18"/>';

        $eye.html(eyeOpen);

        $toggle.on('click', function() {
            const isHidden = $password.attr('type') === 'password';
            $password.attr('type', isHidden ? 'text' : 'password');
            $eye.html(isHidden ? eyeClosed : eyeOpen);
            $toggle.attr('aria-label', isHidden ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
            $toggle.attr('aria-pressed', isHidden ? 'true' : 'false');
        });
    });
</script>
@endpush
