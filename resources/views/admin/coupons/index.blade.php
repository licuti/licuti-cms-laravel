@extends('layouts.admin')

@section('title', __('Quản lý Coupons'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('Coupons ✨') }}</h1>
        <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>{{ __('Thêm mới') }}
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('Danh sách Coupons') }} <span class="badge bg-secondary ms-1">{{ $coupons->total() }}</span></h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã (Code)</th>
                            <th>Loại</th>
                            <th>Đã dùng / Giới hạn</th>
                            <th>Hạn sử dụng</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coupon)
                        <tr>
                            <td><strong>{{ $coupon->code }}</strong></td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $coupon->type->label() }}</span>
                            </td>
                            <td>
                                {{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }}
                            </td>
                            <td>
                                @if($coupon->start_date && $coupon->end_date)
                                    {{ $coupon->start_date->format('d/m/Y') }} - {{ $coupon->end_date->format('d/m/Y') }}
                                @else
                                    Không giới hạn
                                @endif
                            </td>
                            <td>
                                <x-admin.toggle
                                    :checked="$coupon->is_active"
                                    action="{{ route('admin.coupons.toggle-status', $coupon->uuid) }}"
                                />
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.coupons.edit', $coupon->uuid) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('admin.coupons.destroy', $coupon->uuid) }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Không có dữ liệu</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($coupons->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $coupons->links() }}
        </div>
        @endif
    </div>
</div>
@endsection