@extends('layouts.admin')

@php
    $paymentMethod = $paymentMethod ?? null;
    $isEdit = !is_null($paymentMethod);
@endphp

@section('title', $isEdit ? __('Sửa Phương thức: :name', ['name' => $paymentMethod->name]) : __('Thêm Phương thức Thanh toán mới'))

@section('content')
    {{-- 1. Page Header --}}
    <x-admin.page-header 
        :title="$isEdit ? __('Sửa Phương thức: :name', ['name' => $paymentMethod->name]) : __('Thêm Phương thức Thanh toán mới')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Phương thức thanh toán'), 'url' => route('admin.payment-methods.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')]
        ]" 
    />

    {{-- 2. Form soạn thảo chính --}}
    <form 
        id="payment-method-form" 
        action="{{ $isEdit ? route('admin.payment-methods.update', $paymentMethod->uuid) : route('admin.payment-methods.store') }}" 
        method="POST"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif
        
        <div class="row align-items-start g-4">

            {{-- ======================================================== --}}
            {{-- CỘT TRÁI: THÔNG TIN PHƯƠNG THỨC & CẤU HÌNH API          --}}
            {{-- ======================================================== --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">
                <x-admin.card :title="__('Thông tin phương thức')">
                    <div class="d-flex flex-column gap-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-admin.form-group 
                                    :label="__('Mã cổng (Code)')" 
                                    name="code" 
                                    required
                                    :description="__('Định danh kỹ thuật (Ví dụ: cod, vnpay, momo, stripe, bank_transfer)...')"
                                    class="mb-0"
                                >
                                    <x-admin.input 
                                        type="text" 
                                        name="code" 
                                        size="sm"
                                        :value="old('code', $paymentMethod?->code)" 
                                        placeholder="vnpay"
                                        required
                                    />
                                </x-admin.form-group>
                            </div>

                            <div class="col-md-6">
                                <x-admin.form-group 
                                    :label="__('Tên hiển thị')" 
                                    name="name" 
                                    required
                                    :description="__('Tên hiển thị với khách hàng tại bước thanh toán.')"
                                    class="mb-0"
                                >
                                    <x-admin.input 
                                        type="text" 
                                        name="name" 
                                        size="sm"
                                        :value="old('name', $paymentMethod?->name)" 
                                        placeholder="{{ __('Thanh toán VNPAY (QR / ATM / Visa)') }}"
                                        required
                                    />
                                </x-admin.form-group>
                            </div>
                        </div>

                        <x-admin.form-group 
                            :label="__('Mô tả / Hướng dẫn thanh toán')" 
                            name="description" 
                            :description="__('Hiển thị thông tin ghi chú hoặc hướng dẫn chuyển khoản tại trang thanh toán.')"
                        >
                            <x-admin.textarea 
                                name="description" 
                                size="sm"
                                rows="3"
                                placeholder="{{ __('Ví dụ: Chuyển khoản đến STK 123456789 - Vietcombank...') }}"
                            >{{ old('description', $paymentMethod?->description) }}</x-admin.textarea>
                        </x-admin.form-group>

                        <x-admin.form-group 
                            :label="__('Cấu hình API (JSON)')" 
                            name="config" 
                            :description="__('Thông số kết nối cổng API ở định dạng JSON hợp lệ.')"
                            class="mb-0"
                        >
                            <x-admin.textarea 
                                name="config" 
                                size="sm"
                                rows="6"
                                class="font-monospace"
                                placeholder='{&quot;endpoint&quot;: &quot;https://...&quot;, &quot;tmn_code&quot;: &quot;...&quot;, &quot;hash_secret&quot;: &quot;...&quot;}'
                            >{{ old('config', isset($paymentMethod) && $paymentMethod->config ? json_encode($paymentMethod->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</x-admin.textarea>
                        </x-admin.form-group>
                    </div>
                </x-admin.card>
            </div>

            {{-- ======================================================== --}}
            {{-- CỘT PHẢI: CÀI ĐẶT SIDEBAR                                --}}
            {{-- ======================================================== --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- HỘP 1: XUẤT BẢN & TRẠNG THÁI --}}
                <x-admin.publish-box
                    :title="__('Xuất bản')"
                    status-name="is_active"
                    :status="$paymentMethod ? (int) $paymentMethod->is_active : 1"
                    :statuses="[1 => __('Hoạt động'), 0 => __('Tạm tắt')]"
                    :show-published-at="false"
                    :index-route="route('admin.payment-methods.index')"
                    :save-label="$isEdit ? __('Cập nhật') : __('Lưu phương thức')"
                />

            </div>
        </div>
    </form>
@endsection