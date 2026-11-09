@extends('layouts.admin')

@section('title', __('Quản lý Giao dịch Thanh toán'))

@section('content')
    {{-- 1. Header --}}
    <x-admin.page-header
        :title="__('Giao dịch thanh toán')"
        :subtitle="__('Theo dõi toàn bộ giao dịch phát sinh từ đơn hàng qua các cổng thanh toán')"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Giao dịch')]
        ]"
    />

    {{-- 2. Bảng dữ liệu --}}
    <x-admin.table :paginator="$payments">
        <x-slot:head>
            <x-admin.table-th>{{ __('Giao dịch') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Đơn hàng') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Cổng thanh toán') }}</x-admin.table-th>
            <x-admin.table-th align="end">{{ __('Số tiền') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Trạng thái') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Thời gian') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($payments as $payment)
            @php
                $statusColor = match ($payment->status) {
                    \App\Enums\PaymentStatus::SUCCESS    => 'success',
                    \App\Enums\PaymentStatus::PENDING    => 'warning',
                    \App\Enums\PaymentStatus::PROCESSING => 'info',
                    \App\Enums\PaymentStatus::FAILED     => 'danger',
                    \App\Enums\PaymentStatus::REFUNDED   => 'secondary',
                    \App\Enums\PaymentStatus::CANCELLED  => 'dark',
                    default                              => 'secondary',
                };

                $actions = [
                    [
                        'label'  => __('Sửa'),
                        'route'  => route('admin.payments.edit', $payment->uuid),
                        'color'  => 'blue',
                    ],
                    [
                        'label'         => __('Xóa'),
                        'route'         => route('admin.payments.destroy', $payment->uuid),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => __('Xóa bản ghi giao dịch?'),
                        'confirm_text'  => __('Bạn có chắc muốn xóa giao dịch ":code"? Bản ghi này là lịch sử thanh toán của đơn hàng.', [
                            'code' => $payment->gateway_transaction_id ?: $payment->uuid,
                        ]),
                        'confirm_btn'   => __('Xóa ngay'),
                    ],
                ];
            @endphp
            <tr class="group">
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary
                        :title="$payment->gateway_transaction_id ?: __('(Chưa có mã giao dịch)')"
                        :subtitle="'MD5: ' . \Illuminate\Support\Str::limit($payment->uuid, 18, '')"
                        :actions="$actions"
                    />
                </td>
                <td class="py-3 px-3">
                    @if($payment->order)
                        <a href="{{ route('admin.orders.show', $payment->order->uuid) }}" class="fw-semibold text-decoration-none">
                            {{ $payment->order->order_number }}
                        </a>
                    @else
                        <span class="text-body-secondary small">{{ __('Đơn đã xóa') }}</span>
                    @endif
                </td>
                <td class="py-3 px-3">
                    <div class="small fw-semibold text-body">{{ $payment->paymentMethod?->name ?? $payment->gateway_code }}</div>
                    <div class="small text-body-secondary">{{ $payment->gateway_code }}</div>
                </td>
                <td class="py-3 px-3 text-end fw-bold text-body">
                    {{ number_format((float) $payment->amount, 0, ',', '.') }} {{ $payment->currency }}
                </td>
                <td class="py-3 px-3 text-center">
                    <x-admin.badge :label="$payment->status?->label() ?? __('Không xác định')" :color="$statusColor" />
                </td>
                <td class="py-3 px-3">
                    <div class="small fw-semibold text-body">{{ $payment->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
                    @if($payment->paid_at)
                        <div class="small text-success">{{ __('Thanh toán lúc') }} {{ $payment->paid_at->format('d/m/Y H:i') }}</div>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-body-secondary">
                    <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mx-auto mb-2 opacity-50">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <div>{{ __('Chưa có giao dịch thanh toán nào.') }}</div>
                    <div class="small">{{ __('Giao dịch xuất hiện khi khách hàng hoàn tất thanh toán đơn hàng qua cổng đã cấu hình.') }}</div>
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection
