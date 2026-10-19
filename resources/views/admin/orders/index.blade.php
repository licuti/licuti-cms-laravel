@extends('layouts.admin')

@section('title', __('Quản lý Đơn hàng'))

@section('content')
    {{-- 1. Header & Actions --}}
    <x-admin.page-header 
        :title="__('Đơn hàng')" 
        :subtitle="__('Theo dõi và xử lý các đơn đặt hàng từ khách hàng')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Đơn hàng')]
        ]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.orders.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>{{ __('Tạo đơn hàng') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- 2. Filter Status Tabs --}}
    <div class="mb-3">
        <ul class="nav nav-pills border-bottom pb-2 gap-1">
            <li class="nav-item">
                <a class="nav-link px-3 py-1 text-sm {{ !request('status') ? 'active' : '' }}" 
                   href="{{ route('admin.orders.index', request()->except('status')) }}">
                    {{ __('Tất cả') }} <span class="badge bg-secondary ms-1">{{ $statusCounts['all'] ?? 0 }}</span>
                </a>
            </li>
            @foreach(\App\Enums\OrderStatus::cases() as $st)
                <li class="nav-item">
                    <a class="nav-link px-3 py-1 text-sm {{ request('status') === $st->value ? 'active' : '' }}" 
                       href="{{ route('admin.orders.index', array_merge(request()->all(), ['status' => $st->value])) }}">
                        {{ $st->label() }} 
                        <span class="badge {{ $st->badgeClass() }} ms-1">
                            {{ $statusCounts[$st->value] ?? 0 }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- 3. Search & Filter Bar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 align-items-center">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="col-md-4">
                    <x-admin.input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="{{ __('Tìm mã đơn, tên, SĐT khách hàng...') }}" 
                        size="sm" 
                    />
                </div>
                <div class="col-md-3">
                    <select name="payment_method_id" class="form-select form-select-sm">
                        <option value="">{{ __('Tất cả cổng thanh toán') }}</option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->id }}" @selected(request('payment_method_id') == $pm->id)>{{ $pm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm" placeholder="{{ __('Từ ngày') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm" placeholder="{{ __('Đến ngày') }}">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">{{ __('Lọc') }}</button>
                    @if(request()->hasAny(['search', 'payment_method_id', 'date_from', 'date_to', 'status']))
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm" title="{{ __('Đặt lại') }}">↺</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- 4. Orders Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">{{ __('Mã đơn hàng') }}</th>
                        <th>{{ __('Khách hàng') }}</th>
                        <th class="text-end">{{ __('Tổng tiền') }}</th>
                        <th>{{ __('Cổng thanh toán') }}</th>
                        <th class="text-center">{{ __('Trạng thái') }}</th>
                        <th>{{ __('Thời gian đặt') }}</th>
                        <th class="text-end" style="width: 100px;">{{ __('Thao tác') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->uuid) }}" class="fw-bold text-decoration-none text-primary">
                                    {{ $order->order_number }}
                                </a>
                                @if($order->isPaid())
                                    <div><span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.7rem;">{{ __('Đã thanh toán') }}</span></div>
                                @else
                                    <div><span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 0.7rem;">{{ __('Chưa thanh toán') }}</span></div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ $order->shippingAddress?->recipient_name ?? $order->user?->name ?? __('Khách vãng lai') }}</div>
                                <div class="text-muted small">{{ $order->shippingAddress?->phone ?? $order->user?->phone ?? '—' }}</div>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                {{ number_format($order->total_amount, 0, ',', '.') }} {{ $order->currency }}
                            </td>
                            <td>
                                @if($order->paymentMethod)
                                    <span class="badge bg-light text-dark border">
                                        {{ $order->paymentMethod->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="{{ $order->status->badgeClass() }}">
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                <div>{{ $order->created_at->format('d/m/Y') }}</div>
                                <div class="text-secondary" style="font-size: 0.75rem;">{{ $order->created_at->format('H:i') }}</div>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.orders.show', $order->uuid) }}" class="btn btn-outline-primary" title="{{ __('Xem chi tiết') }}">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mx-auto mb-2 text-secondary opacity-50">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="mb-0">{{ __('Không tìm thấy đơn hàng nào phù hợp điều kiện.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection