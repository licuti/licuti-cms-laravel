@extends('layouts.admin')
@section('title', 'Quản lý Đánh giá sản phẩm')

@section('content')
    <x-admin.page-header 
        title="Đánh giá sản phẩm" 
        subtitle="Duyệt và quản lý đánh giá từ khách hàng" 
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'],
            ['label' => 'Đánh giá sản phẩm']
        ]">
    </x-admin.page-header>

    {{-- Search & Filter Bar --}}
    <div class="d-flex justify-content-end align-items-center gap-3 mb-2">
        <form action="{{ route('admin.product-reviews.index') }}" method="GET" id="form-filter-reviews" class="d-flex align-items-center gap-2">
            <x-admin.select name="status" class="w-auto" size="sm">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
            </x-admin.select>
            <x-admin.button type="submit" variant="primary" size="sm">Lọc</x-admin.button>
        </form>
    </div>

    <x-admin.table :paginator="$reviews">
        <x-slot:head>
            <x-admin.table-th>Sản phẩm / Người đánh giá</x-admin.table-th>
            <x-admin.table-th>Đánh giá</x-admin.table-th>
            <x-admin.table-th>Nội dung</x-admin.table-th>
            <x-admin.table-th>Trạng thái</x-admin.table-th>
            <x-admin.table-th align="right">Hành động</x-admin.table-th>
        </x-slot:head>
        @forelse($reviews as $review)
            @php
                $statusColor = match($review->status) {
                    'approved' => 'green',
                    'rejected' => 'red',
                    default => 'amber',
                };
                $statusLabel = match($review->status) {
                    'approved' => 'Đã duyệt',
                    'rejected' => 'Từ chối',
                    default => 'Chờ duyệt',
                };
                
                $actions = [];
                if ($review->status !== 'approved') {
                    $actions[] = [
                        'label'         => 'Duyệt',
                        'route'         => route('admin.product-reviews.approve', $review->uuid),
                        'method'        => 'POST',
                        'color'         => 'green',
                    ];
                }
                if ($review->status !== 'rejected') {
                    $actions[] = [
                        'label'         => 'Từ chối',
                        'route'         => route('admin.product-reviews.reject', $review->uuid),
                        'method'        => 'POST',
                        'color'         => 'amber',
                    ];
                }
                
                $actions[] = [
                    'label'         => 'Xóa',
                    'route'         => route('admin.product-reviews.destroy', $review->uuid),
                    'method'        => 'DELETE',
                    'color'         => 'red',
                    'confirm_title' => 'Xóa đánh giá?',
                    'confirm_text'  => 'Bạn có chắc chắn muốn xóa đánh giá này?',
                    'confirm_btn'   => 'Xóa ngay'
                ];
            @endphp
            <tr class="group">
                <td class="py-3 px-3">
                    <div class="fw-medium text-body-emphasis">{{ $review->product?->translate(app()->getLocale())?->name ?? ($review->product?->translations->first()?->name ?? '---') }}</div>
                    <div class="small text-body-secondary mt-1">Bởi: {{ $review->user?->name ?? 'Khách' }} ({{ $review->user?->email ?? '' }})</div>
                </td>
                <td class="py-3 px-3 text-warning fs-5">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $review->rating)
                            ★
                        @else
                            ☆
                        @endif
                    @endfor
                </td>
                <td class="py-3 px-3 small text-body-secondary" style="max-width: 300px; white-space: normal;">
                    {{ \Illuminate\Support\Str::limit($review->content, 100) }}
                </td>
                <td class="py-3 px-3">
                    <x-admin.badge :label="$statusLabel" :color="$statusColor" />
                </td>
                <td class="py-3 px-3 text-end">
                    <x-admin.row-actions :actions="$actions" />
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-4">
                    Không tìm thấy đánh giá nào phù hợp.
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection