@extends('layouts.admin')

@section('title', __('Chi tiết Đơn hàng #:number', ['number' => $order->order_number]))

@section('content')
    <x-admin.page-header 
        :title="__('Đơn hàng #:number', ['number' => $order->order_number])" 
        :subtitle="__('Đặt lúc :date bởi :customer', [
            'date' => $order->created_at->format('d/m/Y H:i'),
            'customer' => $order->shippingAddress?->recipient_name ?? $order->user?->name ?? __('Khách vãng lai')
        ])" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Đơn hàng'), 'url' => route('admin.orders.index')],
            ['label' => $order->order_number]
        ]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.orders.index') }}" variant="secondary">
                &larr; {{ __('Quay lại') }}
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 align-items-start">
        {{-- CỘT CHÍNH (col-lg-8) --}}
        <div class="col-lg-8 d-flex flex-column gap-4">
            {{-- 1. Bảng sản phẩm trong đơn --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-bold">{{ __('Danh sách sản phẩm') }} ({{ $order->items->count() }})</h6>
                    <span class="{{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Sản phẩm') }}</th>
                                <th class="text-end">{{ __('Đơn giá') }}</th>
                                <th class="text-center">{{ __('Số lượng') }}</th>
                                <th class="text-end">{{ __('Giảm giá') }}</th>
                                <th class="text-end">{{ __('Thành tiền') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                        @if($item->product_sku)
                                            <div class="text-muted small">SKU: {{ $item->product_sku }}</div>
                                        @endif
                                        @if($item->meta)
                                            <div class="text-secondary small">
                                                @foreach($item->meta as $k => $v)
                                                    <span class="badge bg-light text-dark border me-1">{{ $k }}: {{ $v }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($item->unit_price, 0, ',', '.') }} {{ $order->currency }}</td>
                                    <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                    <td class="text-end text-danger">
                                        @if($item->discount_amount > 0)
                                            -{{ number_format($item->discount_amount, 0, ',', '.') }} {{ $order->currency }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">{{ number_format($item->subtotal, 0, ',', '.') }} {{ $order->currency }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-medium">{{ __('Tạm tính:') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($order->subtotal, 0, ',', '.') }} {{ $order->currency }}</td>
                            </tr>
                            @if($order->discount_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end fw-medium text-danger">{{ __('Giảm giá đơn hàng:') }}</td>
                                    <td class="text-end fw-semibold text-danger">-{{ number_format($order->discount_amount, 0, ',', '.') }} {{ $order->currency }}</td>
                                </tr>
                            @endif
                            @if($order->shipping_fee > 0)
                                <tr>
                                    <td colspan="4" class="text-end fw-medium">{{ __('Phí vận chuyển:') }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($order->shipping_fee, 0, ',', '.') }} {{ $order->currency }}</td>
                                </tr>
                            @endif
                            @if($order->tax_amount > 0)
                                <tr>
                                    <td colspan="4" class="text-end fw-medium">{{ __('Thuế:') }}</td>
                                    <td class="text-end fw-semibold">{{ number_format($order->tax_amount, 0, ',', '.') }} {{ $order->currency }}</td>
                                </tr>
                            @endif
                            <tr class="table-active border-top">
                                <td colspan="4" class="text-end fw-bold fs-6">{{ __('Tổng cộng:') }}</td>
                                <td class="text-end fw-bold fs-6 text-primary">{{ number_format($order->total_amount, 0, ',', '.') }} {{ $order->currency }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- 2. Nhật ký & Lịch sử trạng thái đơn --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold">{{ __('Lịch sử thay đổi trạng thái') }}</h6>
                </div>
                <div class="card-body p-4">
                    @if($order->statusHistories->isNotEmpty())
                        <div class="timeline position-relative ps-4 border-start border-2 border-primary-subtle ms-2">
                            @foreach($order->statusHistories as $history)
                                <div class="mb-4 position-relative">
                                    <div class="position-absolute bg-primary rounded-circle border border-white" style="width: 12px; height: 12px; left: -22px; top: 4px;"></div>
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <div>
                                            @if($history->from_status)
                                                <span class="{{ $history->from_status->badgeClass() }}">{{ $history->from_status->label() }}</span>
                                                <span class="mx-1 text-muted">&rarr;</span>
                                            @endif
                                            <span class="{{ $history->to_status->badgeClass() }}">{{ $history->to_status->label() }}</span>
                                        </div>
                                        <div class="text-muted small">
                                            {{ $history->created_at->format('d/m/Y H:i:s') }}
                                        </div>
                                    </div>
                                    @if($history->note)
                                        <div class="text-secondary small mt-1 bg-light p-2 rounded">
                                            {{ $history->note }}
                                        </div>
                                    @endif
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        {{ __('Bởi: :name', ['name' => $history->changedBy?->name ?? __('Hệ thống')]) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">{{ __('Chưa có lịch sử thay đổi.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- CỘT PHỤ (col-lg-4) --}}
        <div class="col-lg-4 d-flex flex-column gap-4">
            {{-- 1. Card chuyển đổi trạng thái đơn --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold">{{ __('Xử lý đơn hàng') }}</h6>
                </div>
                    @if($order->hasAvailableTransitions())
                        <form action="{{ route('admin.orders.update-status', $order->uuid) }}" method="POST" class="d-flex flex-column gap-3 mb-3">
                            @csrf
                            @method('PATCH')
                            <div>
                                <label class="form-label fw-semibold text-sm">{{ __('Chuyển trạng thái:') }}</label>
                                <select name="status" class="form-select">
                                    @foreach($statuses as $st)
                                        @if($order->status->canTransitionTo($st) && $st !== $order->status)
                                            <option value="{{ $st->value }}">{{ $st->label() }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label text-sm">{{ __('Ghi chú chuyển đổi (tùy chọn):') }}</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="{{ __('Lý do hoặc thông tin vận đơn...') }}"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">{{ __('Cập nhật trạng thái') }}</button>
                        </form>
                    @endif

                    @if($order->canCancel())
                        @if($order->hasAvailableTransitions())
                            <hr>
                        @endif
                        {{-- Hủy đơn --}}
                        <form action="{{ route('admin.orders.cancel', $order->uuid) }}" method="POST" onsubmit="return confirm('{{ __('Bạn có chắc chắn muốn hủy đơn hàng này?') }}')">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label text-sm text-danger fw-semibold">{{ __('Lý do hủy đơn:') }}</label>
                                <input type="text" name="cancel_reason" required class="form-control form-control-sm" placeholder="{{ __('Nhập lý do hủy...') }}">
                            </div>
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">{{ __('Hủy đơn hàng') }}</button>
                        </form>
                    @endif

                    @if(!$order->hasAvailableTransitions() && !$order->canCancel())
                        <div class="alert alert-secondary mb-0 text-sm">
                            {{ __('Đơn hàng đã ở trạng thái kết thúc (:status) và không thể chuyển đổi tiếp.', ['status' => $order->status->label()]) }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. Card Thông tin giao hàng --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold">{{ __('Thông tin giao hàng') }}</h6>
                </div>
                <div class="card-body">
                    @if($order->shippingAddress)
                        <div class="mb-2">
                            <span class="text-muted text-sm d-block">{{ __('Người nhận:') }}</span>
                            <span class="fw-bold text-dark">{{ $order->shippingAddress->recipient_name }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="text-muted text-sm d-block">{{ __('Số điện thoại:') }}</span>
                            <span class="fw-semibold text-dark">{{ $order->shippingAddress->phone }}</span>
                        </div>
                        @if($order->shippingAddress->email)
                            <div class="mb-2">
                                <span class="text-muted text-sm d-block">{{ __('Email:') }}</span>
                                <span>{{ $order->shippingAddress->email }}</span>
                            </div>
                        @endif
                        <div class="mb-2">
                            <span class="text-muted text-sm d-block">{{ __('Địa chỉ nhận hàng:') }}</span>
                            <span>{{ $order->shippingAddress->full_address }}</span>
                        </div>
                    @else
                        <p class="text-muted mb-0">{{ __('Không có thông tin địa chỉ giao hàng.') }}</p>
                    @endif

                    @if($order->notes)
                        <hr>
                        <div>
                            <span class="text-muted text-sm d-block fw-semibold">{{ __('Ghi chú của khách:') }}</span>
                            <p class="text-secondary small mb-0">{{ $order->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. Card Thanh toán --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold">{{ __('Thanh toán') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="text-muted text-sm d-block">{{ __('Hình thức:') }}</span>
                        <span class="fw-semibold">{{ $order->paymentMethod?->name ?? __('Chưa chọn') }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted text-sm d-block">{{ __('Tình trạng:') }}</span>
                        @if($order->isPaid())
                            <span class="badge bg-success">{{ __('Đã thanh toán') }}</span>
                            <div class="text-muted small mt-1">{{ __('Lúc :date', ['date' => $order->paid_at->format('d/m/Y H:i')]) }}</div>
                        @else
                            <span class="badge bg-warning text-dark">{{ __('Chưa thanh toán') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
