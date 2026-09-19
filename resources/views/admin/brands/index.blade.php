@extends('layouts.admin')
@section('title', __('Quản lý Thương hiệu'))

@section('content')
    <x-admin.page-header 
        :title="__('Danh sách Thương hiệu')" 
        :subtitle="__('Quản lý các nhãn hàng, thương hiệu sản xuất và phân phối sản phẩm')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => '/admin/dashboard'],
            ['label' => __('Thương hiệu')]
        ]">
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.brands.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>{{ __('Thêm thương hiệu') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Search & Filter Bar --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <form action="{{ route('admin.brands.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <x-admin.input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Tìm tên thương hiệu, website...') }}" size="sm" style="min-width:280px;" />
            
            <x-admin.select name="status" class="w-auto" size="sm">
                <option value="">{{ __('Tất cả trạng thái') }}</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ __('Đang hoạt động') }}</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ __('Tạm ẩn') }}</option>
            </x-admin.select>
            
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('Lọc') }}</x-admin.button>
            @if(request()->filled('search') || request()->filled('status'))
                <x-admin.button href="{{ route('admin.brands.index') }}" variant="secondary" size="sm">{{ __('Đặt lại') }}</x-admin.button>
            @endif
        </form>
    </div>

    <x-admin.table :paginator="$brands">
        <x-slot:head>
            <x-admin.table-th>{{ __('Thương hiệu') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Website') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Thứ tự') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Trạng thái') }}</x-admin.table-th>
        </x-slot:head>
        @forelse($brands as $brand)
            @php
                $name = $brand->name;
                $actions = [
                    ['label' => __('Sửa'), 'route' => route('admin.brands.edit', $brand->uuid), 'color' => 'blue'],
                    [
                        'label'         => __('Xóa'),
                        'route'         => route('admin.brands.destroy', $brand->uuid),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => __('Xóa thương hiệu?'),
                        'confirm_text'  => __('Bạn có chắc chắn muốn xóa thương hiệu ":name"?', ['name' => $name]),
                        'confirm_btn'   => __('Xóa ngay')
                    ],
                ];
                $image = $brand->logo_url;
            @endphp
            <tr class="group">
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary 
                        :image="$image" 
                        :title="$name" 
                        :subtitle="'/' . ($brand->slug ?: '---')" 
                        :actions="$actions" />
                </td>
                <td class="py-3 px-3 small">
                    @if($brand->website)
                        <a href="{{ $brand->website }}" target="_blank" class="text-decoration-none text-primary">
                            {{ $brand->website }} <i class="bi bi-box-arrow-up-right small"></i>
                        </a>
                    @else
                        <span class="text-body-secondary fst-italic">{{ __('Chưa cập nhật') }}</span>
                    @endif
                </td>
                <td class="py-3 px-3 text-center small text-body-secondary">
                    {{ $brand->display_order }}
                </td>
                <td class="py-3 px-3 text-center">
                    <x-admin.badge :label="$brand->status_label" :color="$brand->status_color" />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center py-4 text-body-secondary">
                    {{ __('Không tìm thấy thương hiệu nào.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection