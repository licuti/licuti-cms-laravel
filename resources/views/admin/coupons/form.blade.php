@extends('layouts.admin')

@php
    $coupon = $coupon ?? null;
    $isEdit = !is_null($coupon);
@endphp

@section('title', $isEdit ? __('Sửa Mã giảm giá: :code', ['code' => $coupon->code]) : __('Thêm Mã giảm giá mới'))

@section('content')
    {{-- 1. Page Header --}}
    <x-admin.page-header 
        :title="$isEdit ? __('Sửa Mã giảm giá: :code', ['code' => $coupon->code]) : __('Thêm Mã giảm giá mới')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Mã giảm giá'), 'url' => route('admin.coupons.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')]
        ]" 
    />

    {{-- 2. Form soạn thảo chính --}}
    <form 
        id="coupon-form" 
        action="{{ $isEdit ? route('admin.coupons.update', $coupon->uuid) : route('admin.coupons.store') }}" 
        method="POST"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row align-items-start g-4">

            {{-- ======================================================== --}}
            {{-- CỘT TRÁI: THÔNG TIN VÀ CẤU HÌNH GIẢM GIÁ                --}}
            {{-- ======================================================== --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">

                {{-- CARD 1: THÔNG TIN CƠ BẢN --}}
                <x-admin.card :title="__('Thông tin mã giảm giá')">
                    <div class="d-flex flex-column gap-3">
                        <x-admin.form-group 
                            :label="__('Mã Coupon')" 
                            name="code" 
                            required
                            :description="__('Mã chữ và số khách hàng nhập tại bước thanh toán (Ví dụ: SALE50, FREESHIP)...')"
                        >
                            <x-admin.input 
                                type="text" 
                                name="code" 
                                size="sm"
                                :value="old('code', $coupon?->code)" 
                                placeholder="{{ __('Ví dụ: TET2026, SUMMER50...') }}"
                                required
                            />
                        </x-admin.form-group>

                        <x-admin.form-group 
                            :label="__('Tên chương trình')" 
                            name="name"
                            :description="__('Tên chiến dịch khuyến mãi hoặc tên gợi nhớ nội bộ.')"
                        >
                            <x-admin.input 
                                type="text" 
                                name="name" 
                                size="sm"
                                :value="old('name', $coupon?->name)" 
                                placeholder="{{ __('Ví dụ: Giảm giá khai xuân, Tri ân khách hàng...') }}"
                            />
                        </x-admin.form-group>

                        <x-admin.form-group 
                            :label="__('Mô tả')" 
                            name="description"
                            :description="__('Chi tiết thể lệ chương trình hoặc ghi chú áp dụng.')"
                            class="mb-0"
                        >
                            <x-admin.textarea 
                                name="description" 
                                size="sm"
                                rows="3"
                                placeholder="{{ __('Nhập mô tả tóm tắt...') }}"
                            >{{ old('description', $coupon?->description) }}</x-admin.textarea>
                        </x-admin.form-group>
                    </div>
                </x-admin.card>

                {{-- CARD 2: CẤU HÌNH GIÁ TRỊ GIẢM --}}
                <x-admin.card :title="__('Cấu hình giảm giá')">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-admin.form-group 
                                :label="__('Loại giảm giá')" 
                                name="type" 
                                required
                                class="mb-0"
                            >
                                <x-admin.select 
                                    name="type" 
                                    size="sm"
                                    required
                                >
                                    @foreach(\App\Core\Enums\CouponType::cases() as $type)
                                        <option value="{{ $type->value }}" @selected(old('type', $coupon?->type?->value ?? \App\Core\Enums\CouponType::FIXED->value) === $type->value)>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </x-admin.select>
                            </x-admin.form-group>
                        </div>

                        <div class="col-md-6">
                            <x-admin.form-group 
                                :label="__('Giá trị giảm')" 
                                name="value" 
                                required
                                :description="__('Nhập % (ví dụ: 15) hoặc số tiền (ví dụ: 50000).')"
                                class="mb-0"
                            >
                                <x-admin.input 
                                    type="number" 
                                    step="0.01" 
                                    name="value" 
                                    size="sm"
                                    :value="old('value', $coupon?->value ?? '')" 
                                    placeholder="0"
                                    required
                                />
                            </x-admin.form-group>
                        </div>

                        <div class="col-md-6">
                            <x-admin.form-group 
                                :label="__('Đơn hàng tối thiểu')" 
                                name="min_order_value"
                                :description="__('Đơn hàng phải đạt giá trị này mới được dùng mã.')"
                                class="mb-0"
                            >
                                <x-admin.input 
                                    type="number" 
                                    step="0.01" 
                                    name="min_order_value" 
                                    size="sm"
                                    :value="old('min_order_value', $coupon?->min_order_value ?? '')" 
                                    placeholder="0"
                                />
                            </x-admin.form-group>
                        </div>

                        <div class="col-md-6">
                            <x-admin.form-group 
                                :label="__('Số tiền giảm tối đa')" 
                                name="max_discount_amount"
                                :description="__('Giới hạn số tiền giảm tối đa (với mã giảm theo %).')"
                                class="mb-0"
                            >
                                <x-admin.input 
                                    type="number" 
                                    step="0.01" 
                                    name="max_discount_amount" 
                                    size="sm"
                                    :value="old('max_discount_amount', $coupon?->max_discount_amount ?? '')" 
                                    placeholder="0"
                                />
                            </x-admin.form-group>
                        </div>
                    </div>
                </x-admin.card>

                {{-- CARD 3: ĐIỀU KIỆN ÁP DỤNG --}}
                <x-admin.card :title="__('Điều kiện kết hợp')">
                    <div class="d-flex flex-column gap-3">
                        <x-admin.toggle
                            name="cannot_combine_with_sale_items"
                            :label="__('Không áp dụng cho sản phẩm đang giảm giá (Flash Sale)')"
                            :description="__('Bật để ngăn khách dùng thêm mã này cho các sản phẩm đã có chương trình sale.')"
                            :checked="old('cannot_combine_with_sale_items', $coupon?->cannot_combine_with_sale_items ?? false)"
                        />

                        <div class="border-top pt-3">
                            <x-admin.toggle
                                name="stackable"
                                :label="__('Cho phép dùng chung với mã khác (Stackable)')"
                                :description="__('Bật để cho phép kết hợp mã này với mã giảm giá khác trên cùng đơn hàng.')"
                                :checked="old('stackable', $coupon?->stackable ?? false)"
                            />
                        </div>
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
                    :status="$coupon ? (int) $coupon->is_active : 1"
                    :statuses="[1 => __('Hoạt động'), 0 => __('Tạm dừng')]"
                    :show-published-at="false"
                    :index-route="route('admin.coupons.index')"
                    :save-label="$isEdit ? __('Cập nhật') : __('Lưu mã giảm giá')"
                />

                {{-- HỘP 2: GIỚI HẠN SỬ DỤNG --}}
                <x-admin.card :title="__('Giới hạn sử dụng')">
                    <div class="d-flex flex-column gap-3">
                        <x-admin.form-group 
                            :label="__('Tổng số lượt dùng')" 
                            name="usage_limit" 
                            :description="__('Để trống nếu không giới hạn.')"
                        >
                            <x-admin.input 
                                type="number" 
                                name="usage_limit" 
                                size="sm"
                                :value="old('usage_limit', $coupon?->usage_limit)" 
                                placeholder="∞"
                            />
                        </x-admin.form-group>

                        <x-admin.form-group 
                            :label="__('Số lượt / Mỗi khách')" 
                            name="usage_per_user" 
                            :description="__('Số lần tối đa mỗi tài khoản được dùng.')"
                            class="mb-0"
                        >
                            <x-admin.input 
                                type="number" 
                                name="usage_per_user" 
                                size="sm"
                                :value="old('usage_per_user', $coupon?->usage_per_user)" 
                                placeholder="∞"
                            />
                        </x-admin.form-group>
                    </div>
                </x-admin.card>

                {{-- HỘP 3: THỜI GIAN ÁP DỤNG --}}
                <x-admin.card :title="__('Thời gian áp dụng')">
                    <div class="d-flex flex-column gap-3">
                        <x-admin.form-group 
                            :label="__('Ngày bắt đầu')" 
                            name="start_date"
                            :description="__('Bắt đầu có hiệu lực.')"
                        >
                            <x-admin.input 
                                type="datetime-local" 
                                name="start_date" 
                                size="sm"
                                :value="old('start_date', $coupon?->start_date ? $coupon->start_date->format('Y-m-d\TH:i') : '')" 
                            />
                        </x-admin.form-group>

                        <x-admin.form-group 
                            :label="__('Ngày kết thúc')" 
                            name="end_date" 
                            :description="__('Hết hạn sau thời điểm này.')"
                            class="mb-0"
                        >
                            <x-admin.input 
                                type="datetime-local" 
                                name="end_date" 
                                size="sm"
                                :value="old('end_date', $coupon?->end_date ? $coupon->end_date->format('Y-m-d\TH:i') : '')" 
                            />
                        </x-admin.form-group>
                    </div>
                </x-admin.card>

            </div>
        </div>
    </form>
@endsection