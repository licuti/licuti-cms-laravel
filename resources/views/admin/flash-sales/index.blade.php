@extends('layouts.admin')

@section('title', __('Quản lý Flash Sale'))

@section('content')
    {{-- 1. Header & Actions --}}
    <x-admin.page-header 
        :title="__('Danh sách Flash Sale')" 
        :subtitle="__('Quản lý các chương trình Flash Sale chớp nhoáng với giá sốc và giới hạn số lượng')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Flash Sale')]
        ]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.flash-sales.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>{{ __('Thêm mới') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- 2. Filter Tabs --}}
    <x-admin.filter-tabs :items="$filterTabs" :current="$currentTab" route="admin.flash-sales.index" />

    {{-- 3. Bulk Actions + Search/Filter Bar --}}
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
        <form action="{{ route('admin.flash-sales.index') }}" method="GET" id="form-filter-flash-sales" class="d-flex align-items-center gap-2">
            <input type="hidden" name="tab" value="{{ $currentTab }}">
            <x-admin.input 
                type="text" 
                name="search" 
                value="{{ request('search', request('keyword')) }}" 
                placeholder="{{ __('Tìm tên chiến dịch...') }}" 
                size="sm" 
                style="min-width:240px;" 
            />
            <x-admin.button type="submit" variant="primary" size="sm">
                {{ __('Tìm kiếm') }}
            </x-admin.button>
            @if(request('search') || request('keyword'))
                <x-admin.button href="{{ route('admin.flash-sales.index', ['tab' => $currentTab]) }}" variant="secondary" size="sm">
                    {{ __('Đặt lại') }}
                </x-admin.button>
            @endif
        </form>
    </div>

    {{-- Hidden Bulk Action Form --}}
    <form id="form-bulk-action" action="{{ route('admin.flash-sales.bulk') }}" method="POST">
        @csrf
        <input type="hidden" name="bulk_module" value="flash_sales">
        <input type="hidden" name="action" id="bulk-action-input" value="">
    </form>

    {{-- 4. Bảng dữ liệu --}}
    <x-admin.table :paginator="$flashSales">
        <x-slot:head>
            <x-admin.table-th align="center" style="width: 40px;">
                <input type="checkbox" id="check-all" class="form-check-input m-0">
            </x-admin.table-th>
            <x-admin.table-th>{{ __('Chiến dịch Flash Sale') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Thời gian diễn ra') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Sản phẩm') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Trạng thái') }}</x-admin.table-th>
            <x-admin.table-th align="center" style="width: 120px;">{{ __('Kích hoạt') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($flashSales as $item)
            @php
                $statusEnum = $item->status;
                $actions = [
                    ['label' => __('Sửa'), 'route' => route('admin.flash-sales.edit', $item->uuid), 'color' => 'blue'],
                    [
                        'label'         => __('Xóa'),
                        'route'         => route('admin.flash-sales.destroy', $item->uuid),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => __('Xóa chiến dịch Flash Sale?'),
                        'confirm_text'  => __('Bạn có chắc chắn muốn xóa chiến dịch ":name"? Thao tác này không thể hoàn tác.', ['name' => $item->name]),
                        'confirm_btn'   => __('Xóa ngay')
                    ],
                ];
            @endphp
            <tr class="group">
                <td class="py-3 px-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="row-checkbox form-check-input m-0">
                </td>
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary 
                        :title="$item->name ?: __('(Chưa đặt tên)')" 
                        :subtitle="$item->description ? \Illuminate\Support\Str::limit($item->description, 60) : null" 
                        :actions="$actions" 
                    />
                </td>
                <td class="py-3 px-3">
                    <div class="small fw-semibold text-body">
                        {{ $item->starts_at?->format('d/m/Y H:i') }} &rarr; {{ $item->ends_at?->format('d/m/Y H:i') }}
                    </div>
                    @if($statusEnum === \App\Enums\FlashSaleStatus::RUNNING)
                        <div class="small text-success mt-1 d-flex align-items-center gap-1">
                            <span class="spinner-grow spinner-grow-sm text-success" style="width: 0.5rem; height: 0.5rem;" role="status"></span>
                            <span>{{ __('Đang chạy (kết thúc sau :time)', ['time' => $item->ends_at?->diffForHumans(null, true)]) }}</span>
                        </div>
                    @elseif($statusEnum === \App\Enums\FlashSaleStatus::SCHEDULED)
                        <div class="small text-info mt-1">
                            {{ __('Bắt đầu sau :time', ['time' => $item->starts_at?->diffForHumans(null, true)]) }}
                        </div>
                    @elseif($statusEnum === \App\Enums\FlashSaleStatus::ENDED)
                        <div class="small text-body-secondary mt-1">
                            {{ __('Đã kết thúc :time trước', ['time' => $item->ends_at?->diffForHumans(null, true)]) }}
                        </div>
                    @endif
                </td>
                <td class="py-3 px-3 text-center">
                    <span class="badge text-bg-light border fw-semibold">
                        {{ number_format($item->products_count ?? $item->products->count()) }} {{ __('SKU') }}
                    </span>
                </td>
                <td class="py-3 px-3 text-center">
                    <x-admin.badge :label="$statusEnum->label()" :color="$statusEnum->color()" />
                </td>
                <td class="py-3 px-3 text-center">
                    <form action="{{ route('admin.flash-sales.toggle-status', $item->uuid) }}" method="POST" class="d-inline">
                        @csrf
                        <div class="form-check form-switch d-inline-block m-0">
                            <input 
                                type="checkbox" 
                                class="form-check-input cursor-pointer" 
                                role="switch"
                                onchange="this.form.submit()" 
                                @checked($item->is_active)
                            >
                        </div>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-body-secondary">
                    <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mx-auto mb-2 opacity-50">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <div>{{ __('Chưa có chiến dịch Flash Sale nào trong danh sách.') }}</div>
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection