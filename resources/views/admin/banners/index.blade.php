@extends('layouts.admin')
@section('title', __('Quản lý Banner & Quảng cáo'))

@section('content')
    {{-- Page Header --}}
    <x-admin.page-header 
        :title="__('Danh sách Banner & Quảng cáo')" 
        :subtitle="__('Quản lý các banner slider trang chủ, banner quảng cáo khuyến mãi và popup trên website')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Banner & Quảng cáo')]
        ]">
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.banners.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>{{ __('Thêm banner mới') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Search & Filter Bar --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <form action="{{ route('admin.banners.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <x-admin.input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="{{ __('Tìm tiêu đề hoặc liên kết...') }}" 
                size="sm" 
                style="min-width:260px;" 
            />
            
            <x-admin.select name="position" class="w-auto" size="sm">
                <option value="">{{ __('Tất cả vị trí') }}</option>
                @foreach($positions as $posKey => $posLabel)
                    <option value="{{ $posKey }}" {{ request('position') === $posKey ? 'selected' : '' }}>
                        {{ $posLabel }}
                    </option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="is_active" class="w-auto" size="sm">
                <option value="">{{ __('Tất cả trạng thái') }}</option>
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>{{ __('Đang hiển thị') }}</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>{{ __('Tạm ẩn') }}</option>
            </x-admin.select>
            
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('Lọc') }}</x-admin.button>
            @if(request()->filled('search') || request()->filled('position') || request()->filled('is_active'))
                <x-admin.button href="{{ route('admin.banners.index') }}" variant="secondary" size="sm">{{ __('Đặt lại') }}</x-admin.button>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <x-admin.table :paginator="$banners">
        <x-slot:head>
            <x-admin.table-th>{{ __('Banner & Tiêu đề') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Vị trí hiển thị') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Liên kết đích') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Thời gian áp dụng') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Trạng thái') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Thứ tự') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($banners as $banner)
            @php
                $title = $banner->title;
                $imageUrl = $banner->image_url;
                $actions = [
                    ['label' => __('Sửa'), 'route' => route('admin.banners.edit', $banner->uuid), 'color' => 'blue'],
                    [
                        'label'         => __('Xóa'),
                        'route'         => route('admin.banners.destroy', $banner->uuid),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => __('Xóa banner?'),
                        'confirm_text'  => __('Bạn có chắc chắn muốn xóa banner ":title"?', ['title' => $title]),
                        'confirm_btn'   => __('Xóa ngay')
                    ],
                ];
            @endphp
            <tr class="group">
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary 
                        :image="$imageUrl" 
                        :title="$title" 
                        :actions="$actions" 
                    />
                </td>
                <td class="py-3 px-3">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        {{ $positions[$banner->position] ?? $banner->position }}
                    </span>
                </td>
                <td class="py-3 px-3 small">
                    @if($banner->link)
                        <a href="{{ $banner->link }}" target="{{ $banner->target }}" class="text-decoration-none text-primary d-inline-flex align-items-center gap-1">
                            <span class="text-truncate" style="max-width: 200px;">{{ $banner->link }}</span>
                            <i class="bi bi-box-arrow-up-right small"></i>
                        </a>
                    @else
                        <span class="text-body-secondary fst-italic">---</span>
                    @endif
                </td>
                <td class="py-3 px-3 small text-body-secondary">
                    @if($banner->start_date || $banner->end_date)
                        <div><i class="bi bi-calendar-event me-1"></i>{{ $banner->start_date ? $banner->start_date->format('d/m/Y') : '---' }} &rarr; {{ $banner->end_date ? $banner->end_date->format('d/m/Y') : 'Vô thời hạn' }}</div>
                    @else
                        <span class="text-body-secondary">{{ __('Không giới hạn') }}</span>
                    @endif
                </td>
                <td class="py-3 px-3 text-center">
                    @if($banner->is_active)
                        <span class="badge text-bg-success-subtle text-success border border-success-subtle px-2 py-1">
                            {{ __('Đang hiển thị') }}
                        </span>
                    @else
                        <span class="badge text-bg-secondary-subtle text-secondary px-2 py-1">
                            {{ __('Tạm ẩn') }}
                        </span>
                    @endif
                </td>
                <td class="py-3 px-3 text-center text-body-secondary small">
                    {{ $banner->display_order }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-body-secondary">
                    <i class="bi bi-images fs-2 d-block mb-2 text-muted"></i>
                    {{ __('Không tìm thấy banner nào.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection