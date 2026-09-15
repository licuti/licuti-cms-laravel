@extends('layouts.admin')
@section('title', 'Bảng điều khiển Tổng quan')

@section('content')
<x-admin.page-header 
    title="Bảng điều khiển Tổng quan" 
    subtitle="Hệ thống Quản trị E-Commerce chuẩn Layered Architecture (Service - Repository - Action)"
    :breadcrumbs="[['label' => 'Bảng điều khiển']]"
/>

<div class="space-y-8">
    <!-- Welcome Banner -->
    <div class="relative overflow-hidden rounded-lg bg-gradient-to-r from-blue-600 via-blue-500 to-cyan-600 p-8 text-white shadow-xl shadow-blue-500/10">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold uppercase tracking-wider mb-4 border border-white/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Hệ thống đang hoạt động
            </span>
            <h2 class="text-3xl font-extrabold tracking-tight">Chào mừng đến với Licuti CMS! 👋</h2>
            <p class="mt-2 text-blue-100 text-sm leading-relaxed">
                Hệ thống Quản trị E-Commerce chuẩn Layered Architecture (Service - Repository - Action). Mọi thao tác trên trang này được tối ưu hiển thị Server-side trực tiếp từ CSDL siêu nhanh.
            </p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Stat 1: Users -->
        <x-admin.card class="relative overflow-hidden group hover:border-blue-500/50 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Người dùng hệ thống</p>
                    <h3 class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['users_count'] ?? 0) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                <span>↑ Active accounts</span>
                <span class="text-slate-400 dark:text-slate-500">• Server-side</span>
            </div>
        </x-admin.card>

        <!-- Stat 2: Roles -->
        <x-admin.card class="relative overflow-hidden group hover:border-purple-500/50 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Vai trò (Roles)</p>
                    <h3 class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['roles_count'] ?? 0) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs text-purple-600 dark:text-purple-400 font-medium">
                <span>Spati RBAC</span>
                <span class="text-slate-400 dark:text-slate-500">• Đã khởi tạo</span>
            </div>
        </x-admin.card>

        <!-- Stat 3: Permissions -->
        <x-admin.card class="relative overflow-hidden group hover:border-amber-500/50 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Quyền hạn (Permissions)</p>
                    <h3 class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['permissions_count'] ?? 0) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs text-amber-600 dark:text-amber-400 font-medium">
                <span>Phân quyền chi tiết</span>
                <span class="text-slate-400 dark:text-slate-500">• 60+ Quyền</span>
            </div>
        </x-admin.card>

        <!-- Stat 4: Media -->
        <x-admin.card class="relative overflow-hidden group hover:border-rose-500/50 transition-all duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tệp tin Media</p>
                    <h3 class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-slate-100">{{ number_format($stats['media_count'] ?? 0) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 text-xs text-rose-600 dark:text-rose-400 font-medium">
                <span>Spatie Media v11</span>
                <span class="text-slate-400 dark:text-slate-500">• Polymorphic</span>
            </div>
        </x-admin.card>
    </div>

    <!-- Quick Actions & Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-admin.card title="🚀 Thao tác nhanh (Quick Actions)">
            <div class="grid grid-cols-2 gap-4">
                <a href="/admin/users" class="flex items-center gap-3 p-4 rounded-lg bg-slate-50 dark:bg-slate-800/60 hover:bg-blue-50 dark:hover:bg-blue-950/40 border border-slate-200 dark:border-slate-800 transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100">Thêm thành viên</h4>
                        <p class="text-xs text-slate-500">Tạo tài khoản & gán quyền</p>
                    </div>
                </a>

                <a href="/admin/media" class="flex items-center gap-3 p-4 rounded-lg bg-slate-50 dark:bg-slate-800/60 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-slate-200 dark:border-slate-800 transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-rose-600 text-white flex items-center justify-center shadow-md shadow-rose-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900 dark:text-slate-100">Tải ảnh lên</h4>
                        <p class="text-xs text-slate-500">Upload vào thư viện Media</p>
                    </div>
                </a>
            </div>
        </x-admin.card>

        <x-admin.card title="📌 Tiêu chuẩn Kỹ thuật Đang áp dụng">
            <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0 mt-0.5">✓</span>
                    <span><strong>Kiến trúc Phân lớp:</strong> Controller → Service → Repository → Model (Không query DB trực tiếp trên Controller).</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0 mt-0.5">✓</span>
                    <span><strong>Bảo mật UUID:</strong> Toàn bộ định danh người dùng và tệp tin đều dùng mã UUID tránh lộ ID tuần tự.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0 mt-0.5">✓</span>
                    <span><strong>Server-Side Rendering:</strong> Giao diện Blade truyền thống, hiển thị dữ liệu trực tiếp từ Server kết hợp cầu nối AdminUI/AdminTable.</span>
                </li>
            </ul>
        </x-admin.card>
    </div>
</div>
@endsection
