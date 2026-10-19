@extends('layouts.admin')

@section('title', __('Tạo đơn hàng mới'))

@section('content')
    <x-admin.page-header 
        :title="__('Tạo đơn hàng mới')" 
        :subtitle="__('Tạo thủ công đơn hàng cho khách qua điện thoại hoặc trực tiếp')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Đơn hàng'), 'url' => route('admin.orders.index')],
            ['label' => __('Tạo mới')]
        ]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.orders.index') }}" variant="secondary">
                &larr; {{ __('Quay lại') }}
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.orders.store') }}" method="POST">
        @csrf
        <div class="row g-4 align-items-start">
            {{-- CỘT CHÍNH (col-lg-8) --}}
            <div class="col-lg-8 d-flex flex-column gap-4">
                {{-- 1. Thông tin giao hàng --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="card-title mb-0 fw-bold">{{ __('Thông tin khách hàng & Địa chỉ nhận hàng') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-sm">{{ __('Tên người nhận') }} <span class="text-danger">*</span></label>
                                <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" required class="form-control" placeholder="{{ __('Nguyễn Văn A') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-sm">{{ __('Số điện thoại') }} <span class="text-danger">*</span></label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required class="form-control" placeholder="{{ __('0912345678') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-sm">{{ __('Email') }}</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="{{ __('email@example.com') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-sm">{{ __('Địa chỉ số nhà, đường') }} <span class="text-danger">*</span></label>
                                <input type="text" name="address_line" value="{{ old('address_line') }}" required class="form-control" placeholder="{{ __('Số 123 đường ABC') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-sm">{{ __('Phường / Xã') }}</label>
                                <input type="text" name="ward" value="{{ old('ward') }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-sm">{{ __('Quận / Huyện') }}</label>
                                <input type="text" name="district" value="{{ old('district') }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-sm">{{ __('Tỉnh / Thành phố') }}</label>
                                <input type="text" name="province" value="{{ old('province') }}" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label text-sm">{{ __('Ghi chú đơn hàng') }}</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('Giao giờ hành chính, gọi trước khi đến...') }}">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Sản phẩm trong đơn --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0 fw-bold">{{ __('Sản phẩm đặt mua') }} <span class="text-danger">*</span></h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0" id="items-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Tên sản phẩm') }} <span class="text-danger">*</span></th>
                                        <th style="width: 140px;">{{ __('SKU') }}</th>
                                        <th style="width: 160px;">{{ __('Đơn giá (VND)') }} <span class="text-danger">*</span></th>
                                        <th style="width: 100px;">{{ __('Số lượng') }} <span class="text-danger">*</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" name="items[0][product_name]" required class="form-control form-control-sm" placeholder="{{ __('Tên sản phẩm...') }}">
                                        </td>
                                        <td>
                                            <input type="text" name="items[0][product_sku]" class="form-control form-control-sm" placeholder="SKU-001">
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][unit_price]" required min="0" step="1000" class="form-control form-control-sm" placeholder="100000">
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" required min="1" value="1" class="form-control form-control-sm">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CỘT PHỤ (col-lg-4) --}}
            <div class="col-lg-4 d-flex flex-column gap-4">
                {{-- Box hoàn tất --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="card-title mb-0 fw-bold">{{ __('Tổng kết & Đặt hàng') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-sm">{{ __('Phương thức thanh toán') }}</label>
                            <select name="payment_method_id" class="form-select">
                                <option value="">{{ __('— Chưa chọn cổng —') }}</option>
                                @foreach($paymentMethods as $pm)
                                    <option value="{{ $pm->id }}" @selected(old('payment_method_id') == $pm->id)>{{ $pm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-sm">{{ __('Phí giao hàng (VND)') }}</label>
                            <input type="number" name="shipping_fee" value="{{ old('shipping_fee', 0) }}" min="0" step="1000" class="form-control form-control-sm">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-sm">{{ __('Giảm giá (VND)') }}</label>
                            <input type="number" name="discount_amount" value="{{ old('discount_amount', 0) }}" min="0" step="1000" class="form-control form-control-sm">
                        </div>
                        <hr>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            {{ __('Tạo đơn hàng') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection