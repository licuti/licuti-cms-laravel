@extends('layouts.admin')

@section('title', 'Quản lý Theme')

@section('content')
    <x-admin.page-header
        title="Quản lý Theme"
        subtitle="Chọn giao diện storefront và quản lý các theme đã cài đặt."
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => route('admin.dashboard')],
            ['label' => 'Theme']
        ]"
    />

    @if($previewSlug)
        <div class="alert alert-info d-flex align-items-center justify-content-between gap-3" role="status">
            <span>Đang xem trước theme <strong>{{ $previewSlug }}</strong> trong phiên làm việc hiện tại.</span>
            <form action="{{ route('admin.themes.preview.clear') }}" method="POST">
                @csrf
                <x-admin.button type="submit" variant="outline-secondary">Tắt xem trước</x-admin.button>
            </form>
        </div>
    @endif

    <div class="row g-4 row-cols-1 row-cols-md-2 row-cols-xl-3">
        @forelse($themes as $theme)
            @php($isActive = $theme['slug'] === $activeSlug)
            <div class="col">
                <x-admin.card class="h-100">
                    <div class="d-flex flex-column gap-3 h-100">
                        <div class="rounded border bg-body-tertiary d-flex align-items-center justify-content-center" style="height: 170px;">
                            <span class="display-5 fw-semibold text-body-secondary">{{ strtoupper(substr($theme['name'] ?? $theme['slug'], 0, 1)) }}</span>
                        </div>
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div>
                                <h2 class="h5 mb-1">{{ $theme['name'] ?? $theme['slug'] }}</h2>
                                <div class="small text-body-secondary">{{ $theme['slug'] }} · v{{ $theme['version'] ?? '---' }}</div>
                            </div>
                            @if($isActive)
                                <x-admin.badge label="Đang dùng" color="success" />
                            @endif
                        </div>
                        <p class="small text-body-secondary mb-0 flex-grow-1">{{ $theme['description'] ?? 'Chưa có mô tả.' }}</p>
                        <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top">
                            @can('themes.preview')
                                <form action="{{ route('admin.themes.preview', $theme['slug']) }}" method="POST">
                                    @csrf
                                    <x-admin.button type="submit" variant="outline-secondary">Xem trước</x-admin.button>
                                </form>
                            @endcan
                            @can('themes.view')
                                <x-admin.button href="{{ route('admin.themes.customize', $theme['slug']) }}" variant="outline-primary">Tùy biến</x-admin.button>
                            @endcan
                            @if(! $isActive)
                                @can('themes.activate')
                                    <form action="{{ route('admin.themes.activate', $theme['slug']) }}" method="POST">
                                        @csrf
                                        <x-admin.button type="submit" variant="primary">Kích hoạt</x-admin.button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    </div>
                </x-admin.card>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5 text-body-secondary">Chưa có theme hợp lệ nào được cài đặt.</div>
            </div>
        @endforelse
    </div>
@endsection
