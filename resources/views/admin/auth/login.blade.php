@extends('layouts.auth')
@section('title', 'Đăng nhập Quản trị')

@section('content')
<div class="space-y-6">
    <div class="text-center">
        <h2 class="text-2xl font-bold text-white tracking-tight">Chào mừng trở lại!</h2>
        <p class="mt-1 text-sm text-slate-400">Vui lòng đăng nhập bằng tài khoản quản trị viên</p>
    </div>

    <!-- Alert Box for Laravel validation errors -->
    @if($errors->any())
        <div class="p-3.5 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-xs text-center">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
        @csrf
        
        <!-- Email -->
        <div>
            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                Email Quản trị
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                <input type="email" id="email" name="email" required autocomplete="email" placeholder="admin@licuti.com" value="{{ old('email', 'admin@licuti.com') }}"
                    class="w-full pl-11 pr-4 py-3 bg-slate-950/60 border border-slate-700/80 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all text-sm">
            </div>
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                    Mật khẩu
                </label>
                <a href="#" class="text-xs text-blue-400 hover:text-blue-300 transition-colors">Quên mật khẩu?</a>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••" value="password"
                    class="w-full pl-11 pr-11 py-3 bg-slate-950/60 border border-slate-700/80 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all text-sm">
                <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-white transition-colors">
                    <svg id="eye-icon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Remember & Submit -->
        <div class="flex items-center justify-between pt-2">
            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-950 text-blue-600 focus:ring-blue-500/20 focus:ring-offset-0">
                <span class="text-xs text-slate-400">Ghi nhớ đăng nhập</span>
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full mt-4 py-3.5 px-4 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-500 hover:to-cyan-400 text-white font-semibold rounded-lg shadow-lg shadow-blue-500/25 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
            <span>Đăng nhập hệ thống</span>
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dọn dẹp token cũ nếu có
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');

        // Toggle xem mật khẩu
        $('#toggle-password').on('click', function() {
            const input = $('#password');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
            } else {
                input.attr('type', 'password');
            }
        });
    });
</script>
@endpush
