@extends('layouts.admin')

@section('title', __('Quản lý Kho hàng'))

@section('content')
    {{-- 1. Header --}}
    <x-admin.page-header
        :title="__('Kho hàng')"
        :subtitle="__('Danh sách kho trong hệ thống')"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Kho hàng')]
        ]"
    />

    {{-- 2. Cảnh báo module chưa hoàn thiện --}}
    <div class="alert alert-warning border d-flex gap-3 align-items-start" role="alert">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="flex-shrink-0 mt-1">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
        </svg>
        <div>
            <div class="fw-semibold">{{ __('Module Kho hàng chưa được triển khai') }}</div>
            <div class="small mb-0">
                {{ __('Bảng warehouses hiện chỉ có id và timestamps — chưa có tên kho, địa chỉ, liên kết kiểm kê. Trang này chưa hiển thị nút Thêm mới/Sửa vì form và schema chưa khả dụng.') }}
            </div>
        </div>
    </div>

    {{-- 3. Bảng dữ liệu --}}
    <x-admin.table :paginator="$warehouses">
        <x-slot:head>
            <x-admin.table-th style="width: 120px;">{{ __('ID') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Ngày tạo') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Cập nhật lần cuối') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($warehouses as $warehouse)
            <tr>
                <td class="py-3 px-3 fw-semibold">#{{ $warehouse->id }}</td>
                <td class="py-3 px-3">{{ $warehouse->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                <td class="py-3 px-3">{{ $warehouse->updated_at?->format('d/m/Y H:i') ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center py-5 text-body-secondary">
                    <div>{{ __('Chưa có bản ghi kho hàng nào.') }}</div>
                    <div class="small">{{ __('Dữ liệu sẽ hiển thị sau khi module Kho hàng được hoàn thiện schema và nghiệp vụ.') }}</div>
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection
