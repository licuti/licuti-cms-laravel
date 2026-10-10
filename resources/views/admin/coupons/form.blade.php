@extends('layouts.admin')

@section('title', isset($coupon) ? __('Cập nhật Coupons') : __('Thêm mới Coupons'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ isset($coupon) ? __('Cập nhật Coupons') : __('Thêm mới Coupons') }} ✨</h1>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>{{ __('Quay lại') }}
        </a>
    </div>

    <form action="{{ isset($coupon) ? route('admin.coupons.update', $coupon->uuid) : route('admin.coupons.store') }}" method="POST">
        @csrf
        @if(isset($coupon))
            @method('PUT')
        @endif

        <div class="row">
            <!-- Cột trái: Thông tin cơ bản -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Thông tin chung') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Mã Coupon') }} <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $coupon->code ?? '') }}" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Tên chương trình') }}</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $coupon->name ?? '') }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Mô tả') }}</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $coupon->description ?? '') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Loại giảm giá') }} <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    @foreach(\App\Core\Enums\CouponType::cases() as $type)
                                        <option value="{{ $type->value }}" {{ old('type', isset($coupon) ? $coupon->type->value : '') == $type->value ? 'selected' : '' }}>
                                            {{ $type->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Giá trị giảm') }} <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="value" class="form-control @error('value') is-invalid @enderror" value="{{ old('value', $coupon->value ?? '') }}" required>
                                @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Đơn hàng tối thiểu') }}</label>
                                <input type="number" step="0.01" name="min_order_value" class="form-control @error('min_order_value') is-invalid @enderror" value="{{ old('min_order_value', $coupon->min_order_value ?? '') }}">
                                @error('min_order_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Số tiền giảm tối đa') }}</label>
                                <input type="number" step="0.01" name="max_discount_amount" class="form-control @error('max_discount_amount') is-invalid @enderror" value="{{ old('max_discount_amount', $coupon->max_discount_amount ?? '') }}">
                                @error('max_discount_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Cấu hình nâng cao -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('Giới hạn & Điều kiện') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Tổng số lần sử dụng') }}</label>
                            <input type="number" name="usage_limit" class="form-control @error('usage_limit') is-invalid @enderror" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}" placeholder="Để trống nếu không giới hạn">
                            @error('usage_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Số lần / User') }}</label>
                            <input type="number" name="usage_per_user" class="form-control @error('usage_per_user') is-invalid @enderror" value="{{ old('usage_per_user', $coupon->usage_per_user ?? '') }}" placeholder="Để trống nếu không giới hạn">
                            @error('usage_per_user') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Ngày bắt đầu') }}</label>
                            <input type="datetime-local" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', isset($coupon->start_date) ? $coupon->start_date->format('Y-m-d\TH:i') : '') }}">
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Ngày kết thúc') }}</label>
                            <input type="datetime-local" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', isset($coupon->end_date) ? $coupon->end_date->format('Y-m-d\TH:i') : '') }}">
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <hr>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="is_active">{{ __('Kích hoạt') }}</label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="cannot_combine_with_sale_items" value="1" id="cannot_combine" {{ old('cannot_combine_with_sale_items', $coupon->cannot_combine_with_sale_items ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="cannot_combine">
                                {{ __('Không áp dụng cùng Flash Sale (Sản phẩm đang sale)') }}
                            </label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="stackable" value="1" id="stackable" {{ old('stackable', $coupon->stackable ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="stackable">
                                {{ __('Được phép Stack (Dùng cùng mã khác)') }}
                            </label>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>{{ __('Lưu thay đổi') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection