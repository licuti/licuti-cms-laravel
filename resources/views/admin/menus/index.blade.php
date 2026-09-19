@extends('layouts.admin')
@section('title', __('Quản lý Menu & Điều hướng'))

@section('content')
    {{-- Page Header --}}
    <x-admin.page-header 
        :title="__('Danh sách Menu & Điều hướng')" 
        :subtitle="__('Quản lý hệ thống các menu điều hướng website (Menu chính, Menu chân trang, Menu di động...)')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Menu & Điều hướng')]
        ]">
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.menus.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>{{ __('Thêm menu mới') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Search & Filter Bar --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <form action="{{ route('admin.menus.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <x-admin.input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="{{ __('Tìm tên hoặc mã slug...') }}" 
                size="sm" 
                style="min-width:260px;" 
            />
            
            <x-admin.select name="location" class="w-auto" size="sm">
                <option value="">{{ __('Tất cả vị trí') }}</option>
                @foreach($locations as $locKey => $locLabel)
                    <option value="{{ $locKey }}" {{ request('location') === $locKey ? 'selected' : '' }}>
                        {{ $locLabel }}
                    </option>
                @endforeach
            </x-admin.select>
            
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('Lọc') }}</x-admin.button>
            @if(request()->filled('search') || request()->filled('location'))
                <x-admin.button href="{{ route('admin.menus.index') }}" variant="secondary" size="sm">{{ __('Đặt lại') }}</x-admin.button>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <x-admin.table :paginator="$menus">
        <x-slot:head>
            <x-admin.table-th>{{ __('Tên Menu') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Mã định danh (Slug)') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Vị trí hiển thị') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Số lượng mục') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Trạng thái') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($menus as $menu)
            @php
                $name = $menu->name;
                $actions = [
                    ['label' => __('Sửa'), 'route' => route('admin.menus.edit', $menu->uuid), 'color' => 'blue'],
                    [
                        'label'         => __('Xóa'),
                        'route'         => route('admin.menus.destroy', $menu->uuid),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => __('Xóa menu?'),
                        'confirm_text'  => __('Bạn có chắc chắn muốn xóa menu ":name"? Toàn bộ các mục liên kết thuộc menu này sẽ bị xóa.', ['name' => $name]),
                        'confirm_btn'   => __('Xóa ngay')
                    ],
                ];
            @endphp
            <tr class="group">
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary 
                        :title="$name" 
                        :actions="$actions" 
                    />
                </td>
                <td class="py-3 px-3">
                    <code>{{ $menu->slug }}</code>
                </td>
                <td class="py-3 px-3">
                    @if($menu->location)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                            {{ $locations[$menu->location] ?? $menu->location }}
                        </span>
                    @else
                        <span class="text-body-secondary fst-italic small">{{ __('Chưa gán vị trí') }}</span>
                    @endif
                </td>
                <td class="py-3 px-3 text-center">
                    <span class="badge text-bg-light border font-monospace px-2 py-1">
                        {{ $menu->items->count() }} {{ __('mục') }}
                    </span>
                </td>
                <td class="py-3 px-3 text-center">
                    @if($menu->is_active)
                        <span class="badge text-bg-success-subtle text-success border border-success-subtle px-2 py-1">
                            {{ __('Đang hoạt động') }}
                        </span>
                    @else
                        <span class="badge text-bg-secondary-subtle text-secondary px-2 py-1">
                            {{ __('Tạm khóa') }}
                        </span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-5 text-body-secondary">
                    <i class="bi bi-list-nested fs-2 d-block mb-2 text-muted"></i>
                    {{ __('Không tìm thấy menu nào.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection