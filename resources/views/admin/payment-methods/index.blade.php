@extends('layouts.admin')

@section('title', __('Quản lý Phương thức thanh toán'))

@section('content')
    {{-- 1. Header & Actions --}}
    <x-admin.page-header 
        :title="__('Phương thức Thanh toán')" 
        :subtitle="__('Quản lý các cổng thanh toán và hình thức chi trả đơn hàng')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Phương thức thanh toán')]
        ]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.payment-methods.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>{{ __('Thêm phương thức') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- 2. Bulk Actions + Search/Filter Bar --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
        {{-- Bulk Actions bên trái --}}
        <div class="d-flex align-items-center gap-2">
            <x-admin.select id="bulk-action-select" class="w-auto fw-medium" size="sm">
                <option value="">{{ __('Hành động hàng loạt...') }}</option>
                @foreach($bulkActions as $actionKey => $actionLabel)
                    <option value="{{ $actionKey }}">{{ $actionLabel }}</option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="button" id="btn-apply-bulk" variant="primary" class="flex-shrink-0" size="sm">
                {{ __('Áp dụng') }}
            </x-admin.button>
        </div>

        {{-- Search & Filter Form bên phải --}}
        <form action="{{ route('admin.payment-methods.index') }}" method="GET" id="form-filter-payment-methods" class="d-flex align-items-center gap-2">
            <x-admin.input 
                type="text" 
                name="search" 
                value="{{ request('search', request('keyword')) }}" 
                placeholder="{{ __('Tìm mã cổng, tên phương thức...') }}" 
                size="sm" 
                style="min-width:280px;" 
            />

            <x-admin.select name="status" class="w-auto" size="sm">
                <option value="">{{ __('Tất cả trạng thái') }}</option>
                <option value="1" @selected(request('status') === '1')>{{ __('Hoạt động') }}</option>
                <option value="0" @selected(request('status') === '0')>{{ __('Tạm tắt') }}</option>
            </x-admin.select>

            <x-admin.button type="submit" variant="primary" size="sm">{{ __('Lọc') }}</x-admin.button>
            @if(request()->hasAny(['search', 'keyword', 'status']))
                <x-admin.button href="{{ route('admin.payment-methods.index') }}" variant="secondary" size="sm">{{ __('Đặt lại') }}</x-admin.button>
            @endif
        </form>
    </div>

    {{-- Hidden Bulk Action Form --}}
    <form id="form-bulk-action" action="{{ route('admin.payment-methods.bulk') }}" method="POST">
        @csrf
        <input type="hidden" name="bulk_module" value="payment_methods">
        <input type="hidden" name="action" id="bulk-action-input" value="">
    </form>

    {{-- 3. Bảng dữ liệu --}}
    <x-admin.table :paginator="$paymentMethods">
        <x-slot:head>
            <x-admin.table-th align="center" style="width: 40px;">
                <input type="checkbox" id="check-all" class="form-check-input m-0">
            </x-admin.table-th>
            <x-admin.table-th>{{ __('Tên phương thức') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Mã cổng (Code)') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Mô tả') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Trạng thái') }}</x-admin.table-th>
        </x-slot:head>
        
        @forelse($paymentMethods as $method)
            @php
                $actions = [
                    ['label' => __('Sửa'), 'route' => route('admin.payment-methods.edit', $method->uuid), 'color' => 'blue'],
                    [
                        'label'         => __('Xóa'),
                        'route'         => route('admin.payment-methods.destroy', $method->uuid),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => __('Xóa phương thức thanh toán?'),
                        'confirm_text'  => __('Bạn có chắc chắn muốn xóa phương thức ":name"? Thao tác này không thể hoàn tác.', ['name' => $method->name]),
                        'confirm_btn'   => __('Xóa ngay')
                    ],
                ];
            @endphp
            <tr class="group">
                <td class="py-3 px-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $method->id }}" class="row-checkbox form-check-input m-0">
                </td>
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary 
                        :title="$method->name" 
                        :subtitle="$method->description ?: null" 
                        :actions="$actions" 
                    />
                </td>
                <td class="py-3 px-3">
                    <code class="text-primary">{{ $method->code }}</code>
                </td>
                <td class="py-3 px-3 small text-body-secondary">
                    {{ \Illuminate\Support\Str::limit($method->description ?? __('Chưa có mô tả'), 60) }}
                </td>
                <td class="py-3 px-3 text-center">
                    @if($method->is_active)
                        <x-admin.badge :label="__('Hoạt động')" color="green" />
                    @else
                        <x-admin.badge :label="__('Tạm tắt')" color="default" />
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-5 text-body-secondary">
                    {{ __('Chưa có phương thức thanh toán nào.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection