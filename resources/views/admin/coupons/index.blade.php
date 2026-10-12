@extends('layouts.admin')

@section('title', __('Quản lý Mã giảm giá'))

@section('content')
    {{-- 1. Header & Actions --}}
    <x-admin.page-header 
        :title="__('Danh sách Mã giảm giá')" 
        :subtitle="__('Quản lý các chương trình khuyến mãi và mã voucher giảm giá')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Mã giảm giá')]
        ]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.coupons.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>{{ __('Thêm mới') }}</span>
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
        <form action="{{ route('admin.coupons.index') }}" method="GET" id="form-filter-coupons" class="d-flex align-items-center gap-2">
            <x-admin.input 
                type="text" 
                name="keyword" 
                value="{{ request('keyword', request('search')) }}" 
                placeholder="{{ __('Tìm mã coupon, tên...') }}" 
                size="sm" 
                style="min-width:240px;" 
            />

            <x-admin.select name="type" class="w-auto" size="sm">
                <option value="">{{ __('Tất cả loại') }}</option>
                @foreach(\App\Core\Enums\CouponType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </x-admin.select>

            <x-admin.select name="status" class="w-auto" size="sm">
                <option value="">{{ __('Tất cả trạng thái') }}</option>
                <option value="1" @selected(request('status') === '1')>{{ __('Hoạt động') }}</option>
                <option value="0" @selected(request('status') === '0')>{{ __('Tạm dừng') }}</option>
            </x-admin.select>

            <x-admin.button type="submit" variant="primary" size="sm">{{ __('Lọc') }}</x-admin.button>
            @if(request()->hasAny(['keyword', 'search', 'type', 'status']))
                <x-admin.button href="{{ route('admin.coupons.index') }}" variant="secondary" size="sm">{{ __('Đặt lại') }}</x-admin.button>
            @endif
        </form>
    </div>

    {{-- Hidden Bulk Action Form --}}
    <form id="form-bulk-action" action="{{ route('admin.coupons.bulk') }}" method="POST">
        @csrf
        <input type="hidden" name="bulk_module" value="coupons">
        <input type="hidden" name="action" id="bulk-action-input" value="">
    </form>

    {{-- 3. Bảng dữ liệu --}}
    <x-admin.table :paginator="$coupons">
        <x-slot:head>
            <x-admin.table-th align="center" style="width: 40px;">
                <input type="checkbox" id="check-all" class="form-check-input m-0">
            </x-admin.table-th>
            <x-admin.table-th>{{ __('Mã giảm giá') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Loại & Mức giảm') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Đã dùng / Giới hạn') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Hạn sử dụng') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Trạng thái') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($coupons as $coupon)
            @php
                $actions = [
                    ['label' => __('Sửa'), 'route' => route('admin.coupons.edit', $coupon->uuid), 'color' => 'blue'],
                    [
                        'label'         => __('Xóa'),
                        'route'         => route('admin.coupons.destroy', $coupon->uuid),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => __('Xóa mã giảm giá?'),
                        'confirm_text'  => __('Bạn có chắc chắn muốn xóa mã ":code"? Thao tác này không thể hoàn tác.', ['code' => $coupon->code]),
                        'confirm_btn'   => __('Xóa ngay')
                    ],
                ];

                $discountText = match($coupon->type) {
                    \App\Core\Enums\CouponType::PERCENT => number_format($coupon->value, 0) . '%',
                    \App\Core\Enums\CouponType::FIXED => number_format($coupon->value, 0, ',', '.') . ' ₫',
                    \App\Core\Enums\CouponType::FREE_SHIPPING => __('Miễn phí ship'),
                    default => number_format($coupon->value, 0),
                };
            @endphp
            <tr class="group">
                <td class="py-3 px-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $coupon->id }}" class="row-checkbox form-check-input m-0">
                </td>
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary 
                        :title="$coupon->code" 
                        :subtitle="$coupon->name ?: null" 
                        :actions="$actions" 
                    />
                </td>
                <td class="py-3 px-3">
                    <div class="fw-semibold text-body small">{{ $discountText }}</div>
                    <div class="text-body-secondary small">{{ $coupon->type->label() }}</div>
                </td>
                <td class="py-3 px-3 small text-body-secondary">
                    {{ number_format($coupon->used_count) }} / {{ $coupon->usage_limit ? number_format($coupon->usage_limit) : '∞' }}
                </td>
                <td class="py-3 px-3 small text-body-secondary">
                    @if($coupon->start_date && $coupon->end_date)
                        {{ $coupon->start_date->format('d/m/Y') }} – {{ $coupon->end_date->format('d/m/Y') }}
                    @elseif($coupon->end_date)
                        {{ __('Đến :date', ['date' => $coupon->end_date->format('d/m/Y')]) }}
                    @else
                        <span class="text-success">{{ __('Không giới hạn') }}</span>
                    @endif
                </td>
                <td class="py-3 px-3 text-center">
                    @if($coupon->is_active)
                        <x-admin.badge :label="__('Hoạt động')" color="green" />
                    @else
                        <x-admin.badge :label="__('Tạm dừng')" color="default" />
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-body-secondary">
                    {{ __('Không tìm thấy mã giảm giá nào.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection