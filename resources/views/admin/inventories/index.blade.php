@extends('layouts.admin')

@section('title', __('Quản lý Kiểm kê'))

@section('content')
    {{-- 1. Header --}}
    <x-admin.page-header
        :title="__('Kiểm kê')"
        :subtitle="__('Danh sách phiếu kiểm kê kho')"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Kiểm kê')]
        ]"
    />

    {{-- 2. Cảnh báo module chưa hoàn thiện --}}
    <div class="alert alert-warning border d-flex gap-3 align-items-start" role="alert">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="flex-shrink-0 mt-1">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
        </svg>
        <div>
            <div class="fw-semibold">{{ __('Module Kiểm kê chưa được triển khai') }}</div>
            <div class="small mb-0">
                {{ __('Bảng inventories hiện chỉ có id và timestamps — chưa có kho, sản phẩm, số lượng và lịch sử kiểm kê (inventory_histories). Trang này chưa hiển thị nút Thêm mới/Sửa vì form và schema chưa khả dụng.') }}
            </div>
        </div>
    </div>

    {{-- 3. Bảng dữ liệu --}}
    <x-admin.table :paginator="$inventories">
        <x-slot:head>
            <x-admin.table-th style="width: 120px;">{{ __('ID') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Ngày tạo') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Cập nhật lần cuối') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($inventories as $inventory)
            <tr>
                <td class="py-3 px-3 fw-semibold">#{{ $inventory->id }}</td>
                <td class="py-3 px-3">{{ $inventory->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                <td class="py-3 px-3">{{ $inventory->updated_at?->format('d/m/Y H:i') ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center py-5 text-body-secondary">
                    <div>{{ __('Chưa có phiếu kiểm kê nào.') }}</div>
                    <div class="small">{{ __('Dữ liệu sẽ hiển thị sau khi module Kho hàng và Kiểm kê được hoàn thiện.') }}</div>
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection
