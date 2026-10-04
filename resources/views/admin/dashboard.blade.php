@extends('layouts.admin')
@section('title', 'Bảng điều khiển Tổng quan')

@section('content')
<x-admin.page-header 
    title="Bảng điều khiển Tổng quan" 
    subtitle="Chào mừng trở lại! Tổng quan tình trạng và các hoạt động mới nhất trên hệ thống."
    :breadcrumbs="[['label' => 'Bảng điều khiển']]"
/>

<div class="d-flex flex-column gap-4">
    {{-- 1. Slim Welcome Banner (Tối ưu tương phản 100%) --}}
    <div class="card border-0 text-white overflow-hidden shadow-sm position-relative" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #0284c7 100%); border-radius: 0.75rem;">
        <div class="position-absolute end-0 bottom-0 translate-middle-x mb-n5 me-n5 rounded-circle bg-white opacity-10 pointer-events-none" style="width: 240px; height: 240px; filter: blur(40px);"></div>
        <div class="card-body p-3 p-md-4 position-relative z-1 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                {{-- Icon box nền trắng, icon xanh đậm -- tương phản tuyệt đối --}}
                <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; color: #1e40af;">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div>
                    <h5 class="fw-bold text-white mb-1">Xin chào, {{ auth()->user()->name ?? 'Quản trị viên' }}! 👋</h5>
                    <p class="mb-0 small" style="max-width: 620px; color: rgba(255, 255, 255, 0.9);">
                        Hệ thống Licuti CMS đang hoạt động bình thường. Dưới đây là dữ liệu tổng hợp và các cập nhật mới nhất.
                    </p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                {{-- Badge nền trắng, chữ đen đậm, chấm xanh lá -- không bị chìm nền --}}
                <span class="badge bg-white shadow-sm border-0 rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 small" style="color: #0f172a;">
                    <span class="rounded-circle bg-success flex-shrink-0" style="width: 8px; height: 8px; box-shadow: 0 0 6px #10b981;"></span>
                    <span class="fw-semibold">Hệ thống ổn định</span>
                </span>
            </div>
        </div>
    </div>

    {{-- 2. Stats Grid (4 KPI chính - Icons chuẩn từ admin-sidebar) --}}
    <div class="row g-3 g-lg-4">
        <!-- Stat 1: Users -->
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.card class="h-100 border shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase text-body-secondary fw-semibold small" style="letter-spacing: 0.05em; font-size: 0.75rem;">Người dùng</span>
                        <h3 class="mt-2 mb-0 fw-bold text-body fs-2">{{ number_format($stats['users_count'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small">
                    <span class="text-success fw-medium">Tài khoản hoạt động</span>
                    <a href="{{ route('admin.users.index') }}" class="text-decoration-none text-body-secondary">Chi tiết →</a>
                </div>
            </x-admin.card>
        </div>

        <!-- Stat 2: Products -->
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.card class="h-100 border shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase text-body-secondary fw-semibold small" style="letter-spacing: 0.05em; font-size: 0.75rem;">Sản phẩm</span>
                        <h3 class="mt-2 mb-0 fw-bold text-body fs-2">{{ number_format($stats['products_count'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-3 bg-info-subtle text-info d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        {{-- Icon 3D Box từ admin-sidebar --}}
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small">
                    <span class="text-info fw-medium">Danh mục kho hàng</span>
                    <a href="{{ route('admin.products.index') }}" class="text-decoration-none text-body-secondary">Chi tiết →</a>
                </div>
            </x-admin.card>
        </div>

        <!-- Stat 3: Posts -->
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.card class="h-100 border shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase text-body-secondary fw-semibold small" style="letter-spacing: 0.05em; font-size: 0.75rem;">Bài viết</span>
                        <h3 class="mt-2 mb-0 fw-bold text-body fs-2">{{ number_format($stats['posts_count'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-3 bg-warning-subtle text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        {{-- Icon Newspaper/Post từ admin-sidebar --}}
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small">
                    <span class="text-warning fw-medium">Nội dung & Tin tức</span>
                    <a href="{{ route('admin.posts.index') }}" class="text-decoration-none text-body-secondary">Chi tiết →</a>
                </div>
            </x-admin.card>
        </div>

        <!-- Stat 4: Categories -->
        <div class="col-12 col-sm-6 col-xl-3">
            <x-admin.card class="h-100 border shadow-sm">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase text-body-secondary fw-semibold small" style="letter-spacing: 0.05em; font-size: 0.75rem;">Danh mục</span>
                        <h3 class="mt-2 mb-0 fw-bold text-body fs-2">{{ number_format($stats['categories_count'] ?? 0) }}</h3>
                    </div>
                    <div class="rounded-3 bg-danger-subtle text-danger d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        {{-- Icon Folder từ admin-sidebar --}}
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between small">
                    <span class="text-danger fw-medium">Phân loại sản phẩm</span>
                    <a href="{{ route('admin.categories.index') }}" class="text-decoration-none text-body-secondary">Chi tiết →</a>
                </div>
            </x-admin.card>
        </div>
    </div>

    {{-- 3. Hàng 2: Sản phẩm mới & Bài viết mới (Tiêu đề dùng SVG chuẩn từ sidebar) --}}
    <div class="row g-4">
        <!-- Cột trái: Sản phẩm mới cập nhật -->
        <div class="col-12 col-lg-6">
            <x-admin.card class="h-100 border shadow-sm">
                <x-slot:title>
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary flex-shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Sản phẩm mới cập nhật</span>
                </x-slot:title>
                <x-slot:action>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold text-primary">
                        Xem tất cả ({{ $stats['products_count'] }}) →
                    </a>
                </x-slot:action>

                @if($recentProducts->isEmpty())
                    <div class="text-center py-4 text-body-secondary">
                        <svg class="mx-auto mb-2 text-body-tertiary" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <p class="mb-2 small">Chưa có sản phẩm nào được tạo.</p>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-outline-primary">
                            + Thêm sản phẩm đầu tiên
                        </a>
                    </div>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach($recentProducts as $product)
                            @php
                                $imageUrl = $product->primary_image_url;
                                $name = $product->name ?: 'Sản phẩm #' . $product->id;
                            @endphp
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-body-tertiary border border-opacity-50">
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $name }}" class="rounded-2 border flex-shrink-0 object-fit-cover" style="width: 44px; height: 44px;">
                                    @else
                                        <div class="rounded-2 bg-secondary-subtle text-secondary border d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <a href="{{ route('admin.products.edit', $product->uuid) }}" class="fw-semibold text-body text-decoration-none d-block text-truncate small" title="{{ $name }}">
                                            {{ $name }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 small text-body-secondary mt-1">
                                            @if($product->sku)
                                                <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.7rem;">{{ $product->sku }}</span>
                                            @endif
                                            <span class="text-truncate" style="max-width: 140px;">
                                                {{ $product->category?->translated_name ?? 'Chưa phân loại' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end flex-shrink-0 ms-3">
                                    <div class="fw-bold text-body small">
                                        {{ $product->price > 0 ? number_format($product->price, 0, ',', '.') . ' đ' : 'Liên hệ' }}
                                    </div>
                                    <div class="mt-1">
                                        @if($product->status === 'published')
                                            <span class="badge text-bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">Hiển thị</span>
                                        @elseif($product->status === 'draft')
                                            <span class="badge text-bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.7rem;">Bản nháp</span>
                                        @else
                                            <span class="badge text-bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 0.7rem;">{{ ucfirst($product->status) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-admin.card>
        </div>

        <!-- Cột phải: Bài viết mới nhất -->
        <div class="col-12 col-lg-6">
            <x-admin.card class="h-100 border shadow-sm">
                <x-slot:title>
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-warning flex-shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    <span>Bài viết mới nhất</span>
                </x-slot:title>
                <x-slot:action>
                    <a href="{{ route('admin.posts.index') }}" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold text-primary">
                        Xem tất cả ({{ $stats['posts_count'] }}) →
                    </a>
                </x-slot:action>

                @if($recentPosts->isEmpty())
                    <div class="text-center py-4 text-body-secondary">
                        <svg class="mx-auto mb-2 text-body-tertiary" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                        <p class="mb-2 small">Chưa có bài viết nào được đăng.</p>
                        <a href="{{ route('admin.posts.create') }}" class="btn btn-sm btn-outline-primary">
                            + Viết bài đầu tiên
                        </a>
                    </div>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach($recentPosts as $post)
                            @php
                                $imageUrl = $post->image_url;
                                $title = $post->title ?: 'Bài viết #' . $post->id;
                            @endphp
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-body-tertiary border border-opacity-50">
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $title }}" class="rounded-2 border flex-shrink-0 object-fit-cover" style="width: 44px; height: 44px;">
                                    @else
                                        <div class="rounded-2 bg-warning-subtle text-warning border d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <a href="{{ route('admin.posts.edit', $post->uuid) }}" class="fw-semibold text-body text-decoration-none d-block text-truncate small" title="{{ $title }}">
                                            {{ $title }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 small text-body-secondary mt-1">
                                            <span class="text-truncate" style="max-width: 140px;">
                                                {{ $post->category_names }}
                                            </span>
                                            <span>•</span>
                                            <span>{{ $post->published_at ? $post->published_at->format('d/m/Y') : $post->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end flex-shrink-0 ms-3">
                                    @if($post->status === 'published')
                                        <span class="badge text-bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">Đã xuất bản</span>
                                    @elseif($post->status === 'draft')
                                        <span class="badge text-bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.7rem;">Bản nháp</span>
                                    @else
                                        <span class="badge text-bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 0.7rem;">{{ ucfirst($post->status) }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-admin.card>
        </div>
    </div>

    {{-- 4. Hàng 3: Thao tác nhanh & Thành viên mới (Tiêu đề & nút dùng SVG chuẩn từ sidebar) --}}
    <div class="row g-4">
        <!-- Cột trái: Thao tác nhanh (Quick Actions) -->
        <div class="col-12 col-lg-6">
            <x-admin.card class="h-100 border shadow-sm">
                <x-slot:title>
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary flex-shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Thao tác nhanh</span>
                </x-slot:title>
                <div class="row g-3">
                    <!-- Action 1: Add Product -->
                    <div class="col-12 col-sm-6">
                        <a href="{{ route('admin.products.create') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 bg-body-tertiary border text-decoration-none text-body transition-all">
                            <div class="rounded-3 bg-primary text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 42px; height: 42px;">
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h6 class="mb-0 fw-bold fs-6 text-body">Thêm sản phẩm</h6>
                                <small class="text-body-secondary d-block text-truncate">Đăng sản phẩm mới vào kho</small>
                            </div>
                        </a>
                    </div>

                    <!-- Action 2: Write Post -->
                    <div class="col-12 col-sm-6">
                        <a href="{{ route('admin.posts.create') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 bg-body-tertiary border text-decoration-none text-body transition-all">
                            <div class="rounded-3 bg-warning text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 42px; height: 42px;">
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h6 class="mb-0 fw-bold fs-6 text-body">Viết bài mới</h6>
                                <small class="text-body-secondary d-block text-truncate">Soạn bài viết tin tức / blog</small>
                            </div>
                        </a>
                    </div>

                    <!-- Action 3: Add Category -->
                    <div class="col-12 col-sm-6">
                        <a href="{{ route('admin.categories.create') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 bg-body-tertiary border text-decoration-none text-body transition-all">
                            <div class="rounded-3 bg-danger text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 42px; height: 42px;">
                                {{-- Folder icon từ sidebar --}}
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h6 class="mb-0 fw-bold fs-6 text-body">Thêm danh mục</h6>
                                <small class="text-body-secondary d-block text-truncate">Tạo phân loại sản phẩm</small>
                            </div>
                        </a>
                    </div>

                    <!-- Action 4: Upload Media -->
                    <div class="col-12 col-sm-6">
                        <a href="{{ route('admin.media.index') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 bg-body-tertiary border text-decoration-none text-body transition-all">
                            <div class="rounded-3 bg-info text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 42px; height: 42px;">
                                {{-- Media icon từ sidebar --}}
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h6 class="mb-0 fw-bold fs-6 text-body">Thư viện Media</h6>
                                <small class="text-body-secondary d-block text-truncate">Tải lên & quản lý tệp tin</small>
                            </div>
                        </a>
                    </div>

                    <!-- Action 5: Add Member -->
                    <div class="col-12 col-sm-6">
                        <a href="{{ route('admin.users.create') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 bg-body-tertiary border text-decoration-none text-body transition-all">
                            <div class="rounded-3 bg-success text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 42px; height: 42px;">
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h6 class="mb-0 fw-bold fs-6 text-body">Thêm thành viên</h6>
                                <small class="text-body-secondary d-block text-truncate">Tạo tài khoản quản trị mới</small>
                            </div>
                        </a>
                    </div>

                    <!-- Action 6: Settings -->
                    <div class="col-12 col-sm-6">
                        <a href="{{ route('admin.settings.edit') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 bg-body-tertiary border text-decoration-none text-body transition-all">
                            <div class="rounded-3 bg-secondary text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 42px; height: 42px;">
                                {{-- Settings icon từ sidebar --}}
                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h6 class="mb-0 fw-bold fs-6 text-body">Cài đặt hệ thống</h6>
                                <small class="text-body-secondary d-block text-truncate">Cấu hình chung website</small>
                            </div>
                        </a>
                    </div>
                </div>
            </x-admin.card>
        </div>

        <!-- Cột phải: Thành viên mới & Phân quyền -->
        <div class="col-12 col-lg-6">
            <x-admin.card class="h-100 border shadow-sm">
                <x-slot:title>
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-success flex-shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Thành viên mới</span>
                </x-slot:title>
                <x-slot:action>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-link text-decoration-none p-0 fw-semibold text-primary">
                        Quản lý ({{ $stats['users_count'] }}) →
                    </a>
                </x-slot:action>

                @if($recentUsers->isEmpty())
                    <div class="text-center py-4 text-body-secondary">
                        <p class="mb-0 small">Chưa có người dùng nào.</p>
                    </div>
                @else
                    <div class="d-flex flex-column gap-2">
                        @foreach($recentUsers as $user)
                            <div class="d-flex align-items-center justify-content-between p-2 rounded-2 bg-body-tertiary border border-opacity-50">
                                <div class="d-flex align-items-center gap-3 min-w-0">
                                    @php
                                        $avatarUrl = $user->getAvatarUrl();
                                    @endphp
                                    @if($avatarUrl)
                                        <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="rounded-circle border flex-shrink-0 object-fit-cover" style="width: 40px; height: 40px;">
                                    @else
                                        <div class="rounded-circle bg-primary-subtle text-primary border border-primary-subtle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 40px; height: 40px; font-size: 0.85rem;">
                                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <a href="{{ route('admin.users.edit', $user->uuid) }}" class="fw-semibold text-body text-decoration-none d-block text-truncate small" title="{{ $user->name }}">
                                            {{ $user->name }}
                                        </a>
                                        <small class="text-body-secondary d-block text-truncate">
                                            {{ $user->email }}
                                        </small>
                                    </div>
                                </div>

                                <div class="text-end flex-shrink-0 ms-3">
                                    <div class="d-flex align-items-center gap-1 justify-content-end flex-wrap">
                                        @forelse($user->roles as $role)
                                            <span class="badge text-bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size: 0.7rem;">
                                                {{ $role->name }}
                                            </span>
                                        @empty
                                            <span class="badge text-bg-light text-body-secondary border" style="font-size: 0.7rem;">
                                                Thành viên
                                            </span>
                                        @endforelse
                                    </div>
                                    <small class="text-body-secondary d-block mt-1" style="font-size: 0.7rem;">
                                        {{ $user->created_at ? $user->created_at->diffForHumans() : '' }}
                                    </small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-admin.card>
        </div>
    </div>
</div>
@endsection


