@extends('layouts.admin')

@section('title', __('Chi tiết Giỏ hàng :token', ['token' => substr($cart->uuid, 0, 8)]))

@section('content')
    {{-- 1. Header --}}
    <x-admin.page-header 
        :title="__('Chi tiết Giỏ hàng')" 
        :subtitle="__('Mã token: :uuid', ['uuid' => $cart->uuid])" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Giỏ hàng'), 'url' => route('admin.carts.index')],
            ['label' => substr($cart->uuid, 0, 8)]
        ]"
    >
        <x-slot:actions>
            <x-admin.button variant="secondary" href="{{ route('admin.carts.index') }}">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>{{ __('Quay lại') }}</span>
            </x-admin.button>

            @can('carts.delete')
                <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" onclick="confirmDeleteCart()">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>{{ __('Xóa giỏ hàng') }}</span>
                </button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Cảnh báo các vấn đề nếu có (hết hàng, biến động giá) --}}
    @if(!empty($summary->issues))
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-start gap-2 mb-4">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-warning flex-shrink-0 mt-1">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <strong class="d-block mb-1">{{ __('Phát hiện cảnh báo đối với giỏ hàng này:') }}</strong>
                <ul class="mb-0 ps-3">
                    @foreach($summary->issues as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row g-4 align-items-start">
        {{-- CỘT CHÍNH: Bảng sản phẩm trong giỏ --}}
        <div class="col-lg-8 d-flex flex-column gap-4">
            <x-admin.card :title="__('Danh sách sản phẩm (:count)', ['count' => count($summary->lines)])">
                @if(empty($summary->lines))
                    <div class="text-center py-5 text-muted">
                        <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mb-2 opacity-50">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="mb-0">{{ __('Giỏ hàng hiện không có sản phẩm nào.') }}</p>
                    </div>
                @else
                    <div class="table-responsive -mx-3 -my-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted text-xs text-uppercase">
                                <tr>
                                    <th>{{ __('Sản phẩm') }}</th>
                                    <th class="text-end">{{ __('Giá lưu (Snapshot)') }}</th>
                                    <th class="text-end">{{ __('Giá hiện tại') }}</th>
                                    <th class="text-center">{{ __('SL') }}</th>
                                    <th class="text-end">{{ __('Thành tiền') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($summary->lines as $line)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                @if(!empty($line->imageUrl))
                                                    <img src="{{ $line->imageUrl }}" alt="{{ $line->name }}" class="rounded object-fit-cover border" width="48" height="48">
                                                @else
                                                    <div class="rounded bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 48px; height: 48px;">
                                                        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $line->name }}</div>
                                                    @if($line->variantName)
                                                        <div class="text-muted small">
                                                            <span class="badge bg-light text-dark border">{{ $line->variantName }}</span>
                                                        </div>
                                                    @endif
                                                    @if($line->sku)
                                                        <div class="text-muted text-xs">SKU: <code>{{ $line->sku }}</code></div>
                                                    @endif

                                                    @if(!empty($line->issues))
                                                        <div class="mt-1">
                                                            @foreach($line->issues as $lineIssue)
                                                                <span class="badge bg-danger-subtle text-danger">{{ $lineIssue }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-muted">{{ number_format($line->snapshotPrice, 0, ',', '.') }} đ</span>
                                        </td>
                                        <td class="text-end">
                                            @if($line->unitPrice !== $line->snapshotPrice)
                                                <span class="fw-semibold text-warning">
                                                    {{ number_format($line->unitPrice, 0, ',', '.') }} đ
                                                </span>
                                                <div class="text-xs text-muted">
                                                    ({{ $line->unitPrice > $line->snapshotPrice ? '+' : '' }}{{ number_format($line->unitPrice - $line->snapshotPrice, 0, ',', '.') }} đ)
                                                </div>
                                            @else
                                                <span class="text-dark">{{ number_format($line->unitPrice, 0, ',', '.') }} đ</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                                x{{ $line->quantity }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-semibold text-dark">
                                            {{ number_format($line->lineTotal, 0, ',', '.') }} đ
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-admin.card>

            {{-- Ghi chú kỹ thuật giỏ hàng --}}
            <x-admin.card :title="__('Dữ liệu kỹ thuật')">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">{{ __('Cart ID nội bộ:') }}</label>
                        <div><code>#{{ $cart->id }}</code></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">{{ __('Tiền tệ:') }}</label>
                        <div><span class="badge bg-light text-dark border">{{ $cart->currency ?? 'VND' }}</span></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">{{ __('Thời gian tạo:') }}</label>
                        <div class="text-dark">{{ $cart->created_at?->format('d/m/Y H:i:s') ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small mb-1">{{ __('Cập nhật lần cuối:') }}</label>
                        <div class="text-dark">{{ $cart->updated_at?->format('d/m/Y H:i:s') ?? '—' }}</div>
                    </div>
                </div>
            </x-admin.card>
        </div>

        {{-- SIDEBAR: Thông tin khách + Tóm tắt thanh toán --}}
        <div class="col-lg-4 d-flex flex-column gap-4">
            {{-- Trạng thái & Khách hàng --}}
            <x-admin.card :title="__('Thông tin Giỏ hàng')">
                <div class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-label text-muted small mb-1">{{ __('Trạng thái:') }}</label>
                        <div>
                            <span class="badge {{ $cart->status->badgeClass() }} px-2 py-1">
                                {{ $cart->status->label() }}
                            </span>
                        </div>
                    </div>

                    @if($cart->status->value === 'converted' && $cart->convertedOrder)
                        <div class="p-2 rounded bg-success-subtle border border-success-subtle">
                            <label class="form-label text-success small mb-1 fw-semibold">{{ __('Đã chuyển thành Đơn hàng:') }}</label>
                            <div>
                                <a href="{{ route('admin.orders.show', $cart->convertedOrder->uuid) }}" class="fw-bold text-success text-decoration-none">
                                    #{{ $cart->convertedOrder->order_number ?? substr($cart->convertedOrder->uuid, 0, 8) }} &rarr;
                                </a>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="form-label text-muted small mb-1">{{ __('Khách hàng:') }}</label>
                        @if($cart->user)
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">
                                    {{ strtoupper(substr($cart->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $cart->user->name }}</div>
                                    <div class="text-muted text-xs">{{ $cart->user->email }}</div>
                                </div>
                            </div>
                        @else
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-sm rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">
                                    G
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ __('Khách vãng lai (Guest)') }}</div>
                                    @if($cart->guest_email)
                                        <div class="text-muted text-xs">{{ $cart->guest_email }}</div>
                                    @else
                                        <div class="text-muted text-xs">{{ __('Chưa cung cấp email') }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="form-label text-muted small mb-1">{{ __('X-Cart-Token (UUID):') }}</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control font-monospace" id="cart-token-input" value="{{ $cart->uuid }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyToken()" title="{{ __('Sao chép') }}">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="form-label text-muted small mb-1">{{ __('Hoạt động cuối:') }}</label>
                        <div class="text-dark">
                            {{ $cart->last_activity_at?->diffForHumans() ?? '—' }}
                            <span class="text-muted text-xs">({{ $cart->last_activity_at?->format('d/m/Y H:i') ?? '—' }})</span>
                        </div>
                    </div>
                </div>
            </x-admin.card>

            {{-- Tóm tắt chi phí --}}
            <x-admin.card :title="__('Tóm tắt Đơn giá')">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">{{ __('Tổng số lượng:') }}</span>
                        <span class="fw-semibold">{{ $summary->itemCount }} {{ __('sản phẩm') }}</span>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">{{ __('Tạm tính:') }}</span>
                        <span class="fw-semibold">{{ number_format($summary->subtotal, 0, ',', '.') }} đ</span>
                    </div>

                    @if($summary->coupon)
                        <div class="d-flex justify-content-between align-items-center text-success">
                            <span>
                                {{ __('Giảm giá:') }}
                                <code class="text-success fw-bold">{{ $summary->coupon->code }}</code>
                            </span>
                            <span class="fw-semibold">-{{ number_format($summary->discountAmount, 0, ',', '.') }} đ</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <span class="text-muted">{{ __('Phí vận chuyển:') }}</span>
                        @if($summary->shippingFee == 0)
                            <span class="badge bg-success-subtle text-success">{{ __('Miễn phí') }}</span>
                        @else
                            <span class="fw-semibold">{{ number_format($summary->shippingFee, 0, ',', '.') }} đ</span>
                        @endif
                    </div>

                    <hr class="my-2">

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-6">{{ __('Tổng thanh toán:') }}</span>
                        <span class="fw-bold fs-5 text-primary">{{ number_format($summary->grandTotal, 0, ',', '.') }} đ</span>
                    </div>
                </div>
            </x-admin.card>
        </div>
    </div>

    {{-- Script Sao chép & Xóa --}}
    @push('scripts')
    <script>
        function copyToken() {
            const input = document.getElementById('cart-token-input');
            input.select();
            input.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(input.value).then(() => {
                if (typeof toastr !== 'undefined') {
                    toastr.success('{{ __('Đã sao chép Cart Token vào clipboard!') }}');
                } else {
                    alert('{{ __('Đã sao chép Cart Token!') }}');
                }
            });
        }

        function confirmDeleteCart() {
            if (!confirm('{{ __('Bạn có chắc chắn muốn xóa giỏ hàng này? Thao tác này không thể hoàn tác.') }}')) {
                return;
            }

            fetch('{{ route('admin.carts.destroy', $cart->uuid) }}', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route('admin.carts.index') }}';
                } else {
                    alert(data.message || '{{ __('Không thể xóa giỏ hàng.') }}');
                }
            })
            .catch(err => {
                console.error(err);
                alert('{{ __('Đã có lỗi xảy ra. Vui lòng thử lại.') }}');
            });
        }
    </script>
    @endpush
@endsection
