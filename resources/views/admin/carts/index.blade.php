@extends('layouts.admin')

@section('title', __('Quản lý Giỏ hàng'))

@section('content')
    {{-- 1. Header --}}
    <x-admin.page-header 
        :title="__('Giỏ hàng')" 
        :subtitle="__('Theo dõi các giỏ hàng đang hoạt động, bỏ quên hoặc đã chuyển đổi của khách hàng')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Giỏ hàng')]
        ]"
    />

    {{-- 2. Filter Status Tabs --}}
    <div class="mb-3">
        <ul class="nav nav-pills border-bottom pb-2 gap-1">
            <li class="nav-item">
                <a class="nav-link px-3 py-1 text-sm {{ !request('status') ? 'active' : '' }}" 
                   href="{{ route('admin.carts.index', request()->except('status')) }}">
                    {{ __('Tất cả') }} <span class="badge bg-secondary ms-1">{{ $statusCounts['all'] ?? 0 }}</span>
                </a>
            </li>
            @foreach(\App\Enums\CartStatus::cases() as $st)
                <li class="nav-item">
                    <a class="nav-link px-3 py-1 text-sm {{ request('status') === $st->value ? 'active' : '' }}" 
                       href="{{ route('admin.carts.index', array_merge(request()->all(), ['status' => $st->value])) }}">
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
            <form action="{{ route('admin.carts.index') }}" method="GET" class="row g-2 align-items-center">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="col-md-5">
                    <x-admin.input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="{{ __('Tìm theo mã token, email, tên khách hàng...') }}" 
                    />
                </div>
                <div class="col-auto">
                    <x-admin.button type="submit" variant="secondary">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span>{{ __('Tìm kiếm') }}</span>
                    </x-admin.button>
                </div>
                @if(request()->anyFilled(['search', 'status']))
                    <div class="col-auto">
                        <a href="{{ route('admin.carts.index') }}" class="btn btn-link text-decoration-none text-muted">
                            {{ __('Xóa lọc') }}
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- 4. Table card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            {{-- Bulk actions bar --}}
            <div id="bulk-actions-bar" class="p-3 bg-light border-bottom d-none align-items-center justify-content-between">
                <div>
                    <span id="selected-count" class="fw-bold">0</span> {{ __('mục được chọn') }}
                </div>
                <div class="d-flex gap-2">
                    @foreach($bulkActions as $actionKey => $actionLabel)
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="executeBulkAction('{{ $actionKey }}')">
                            {{ $actionLabel }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted text-uppercase text-xs">
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" class="form-check-input" id="check-all" onclick="toggleCheckAll(this)">
                            </th>
                            <th>{{ __('Token Giỏ Hàng') }}</th>
                            <th>{{ __('Khách Hàng') }}</th>
                            <th class="text-center">{{ __('Số lượng SP') }}</th>
                            <th>{{ __('Mã Giảm Giá') }}</th>
                            <th>{{ __('Hoạt Động Cuối') }}</th>
                            <th>{{ __('Trạng Thái') }}</th>
                            <th style="width: 120px;" class="text-end">{{ __('Thao tác') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($carts as $cart)
                            <tr id="row-{{ $cart->uuid }}">
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input row-checkbox" value="{{ $cart->uuid }}" onchange="updateSelectedCount()">
                                </td>
                                <td>
                                    <a href="{{ route('admin.carts.show', $cart->uuid) }}" class="fw-bold text-decoration-none font-monospace">
                                        {{ substr($cart->uuid, 0, 8) }}...
                                    </a>
                                </td>
                                <td>
                                    @if($cart->user)
                                        <div>
                                            <span class="fw-semibold text-dark">{{ $cart->user->name }}</span>
                                            <div class="text-muted small">{{ $cart->user->email }}</div>
                                        </div>
                                    @elseif($cart->guest_email)
                                        <div>
                                            <span class="badge bg-light text-dark border">{{ __('Khách') }}</span>
                                            <span class="text-muted small ms-1">{{ $cart->guest_email }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">{{ __('Khách vãng lai') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill">
                                        {{ $cart->items->sum('quantity') }}
                                    </span>
                                </td>
                                <td>
                                    @if($cart->coupon)
                                        <span class="badge bg-light text-primary border border-primary">
                                            {{ $cart->coupon->code }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="small text-muted" title="{{ $cart->last_activity_at }}">
                                        {{ $cart->last_activity_at ? $cart->last_activity_at->diffForHumans() : '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="{{ $cart->status->badgeClass() }}">
                                        {{ $cart->status->label() }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.carts.show', $cart->uuid) }}" class="btn btn-outline-secondary" title="{{ __('Xem chi tiết') }}">
                                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" onclick="deleteCart('{{ $cart->uuid }}')" title="{{ __('Xóa giỏ hàng') }}">
                                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mb-2 opacity-50">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                                    </svg>
                                    <p class="mb-0">{{ __('Không có giỏ hàng nào phù hợp.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($carts->hasPages())
                <div class="p-3 border-top d-flex justify-content-end">
                    {{ $carts->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function toggleCheckAll(master) {
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = master.checked);
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const selected = document.querySelectorAll('.row-checkbox:checked').length;
        const bar = document.getElementById('bulk-actions-bar');
        document.getElementById('selected-count').innerText = selected;
        if (selected > 0) {
            bar.classList.remove('d-none');
            bar.classList.add('d-flex');
        } else {
            bar.classList.add('d-none');
            bar.classList.remove('d-flex');
        }
    }

    function deleteCart(uuid) {
        if (!confirm('{{ __("Bạn có chắc chắn muốn xóa giỏ hàng này?") }}')) return;

        fetch(`{{ url('admin/carts') }}/${uuid}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById(`row-${uuid}`);
                if (row) row.remove();
            } else {
                alert(data.message || '{{ __("Có lỗi xảy ra") }}');
            }
        })
        .catch(err => alert('{{ __("Lỗi kết nối máy chủ") }}'));
    }

    function executeBulkAction(action) {
        const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (ids.length === 0) return;

        function doBulk() {
            fetch(`{{ route('admin.carts.bulk') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ bulk_module: 'carts', action: action, ids: ids })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    if (typeof AdminUI !== 'undefined') {
                        AdminUI.alert('{{ __("Lỗi") }}', data.message || '{{ __("Có lỗi xảy ra") }}', 'error');
                    } else {
                        alert(data.message || '{{ __("Có lỗi xảy ra") }}');
                    }
                }
            })
            .catch(err => {
                if (typeof AdminUI !== 'undefined') {
                    AdminUI.alert('{{ __("Lỗi") }}', '{{ __("Lỗi kết nối máy chủ") }}', 'error');
                } else {
                    alert('{{ __("Lỗi kết nối máy chủ") }}');
                }
            });
        }

        if (typeof AdminUI !== 'undefined') {
            AdminUI.confirm({
                title: '{{ __("Xác nhận thao tác") }}',
                text: '{{ __("Bạn có chắc chắn muốn thực hiện hành động này trên các giỏ hàng đã chọn?") }}',
                confirmText: '{{ __("Thực hiện") }}',
                cancelText: '{{ __("Hủy") }}'
            }, doBulk);
        } else {
            if (confirm('{{ __("Bạn có chắc chắn muốn thực hiện hành động này trên các giỏ hàng đã chọn?") }}')) {
                doBulk();
            }
        }
    }
</script>
@endpush
