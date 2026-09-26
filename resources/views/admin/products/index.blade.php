@extends('layouts.admin')
@section('title', __('Quản lý Sản phẩm'))

@section('content')
    {{-- Page Header --}}
    <x-admin.page-header 
        :title="__('Danh sách Sản phẩm')" 
        :subtitle="__('Quản lý thông tin sản phẩm, giá bán, tồn kho và các biến thể thương mại điện tử')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Sản phẩm')]
        ]">
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.products.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>{{ __('Thêm sản phẩm mới') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Tab Navigation --}}
    <x-admin.filter-tabs :items="$tabs" :current="$tab" route="admin.products.index" />

    {{-- Bulk Actions + Search/Filter Bar --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
        {{-- Bulk Actions --}}
        <div class="d-flex align-items-center gap-2">
            <x-admin.select id="bulk-action-select" class="w-auto fw-medium" size="sm">
                <option value="">{{ __('Hành động hàng loạt...') }}</option>
                @foreach($bulkActions as $actionKey => $actionLabel)
                    <option value="{{ $actionKey }}">{{ $actionLabel }}</option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="button" id="btn-apply-bulk" variant="primary" class="flex-shrink-0" size="sm">{{ __('Áp dụng') }}</x-admin.button>
        </div>

        {{-- Search & Filter Form --}}
        <form action="{{ route('admin.products.index') }}" method="GET" id="form-filter-products" class="d-flex align-items-center gap-2">
            <x-admin.input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Tìm tên sản phẩm, mã SKU, barcode...') }}" size="sm" style="min-width:260px;">
            </x-admin.input>
            <x-admin.select name="category_id" class="w-auto" size="sm">
                <option value="">{{ __('Tất cả danh mục') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->translated_name }}
                    </option>
                @endforeach
            </x-admin.select>
            <x-admin.select name="brand_id" class="w-auto" size="sm">
                <option value="">{{ __('Tất cả thương hiệu') }}</option>
                @foreach($brands as $b)
                    <option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->name }}
                    </option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('Lọc') }}</x-admin.button>
        </form>
    </div>

    {{-- Hidden Bulk Action Form --}}
    <form id="form-bulk-action" action="{{ route('admin.products.bulk') }}" method="POST">
        @csrf
        <input type="hidden" name="bulk_module" value="products">
        <input type="hidden" name="action" id="bulk-action-input" value="">
    </form>

    {{-- Table --}}
    <x-admin.table :paginator="$products">
        <x-slot:head>
            <x-admin.table-th padding="text-center" align="center">
                <input type="checkbox" id="check-all" class="form-check-input">
            </x-admin.table-th>
            <x-admin.table-th>{{ __('Sản phẩm') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Phân loại') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Giá bán') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Tồn kho') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Trạng thái') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($products as $product)
            @php
                $name = $product->name;
                $imageUrl = $product->primary_image_url;
                $sku = $product->sku ? 'SKU: ' . $product->sku : '---';
                $actions = [
                    ['label' => __('Sửa'), 'route' => route('admin.products.edit', $product->uuid), 'color' => 'blue'],
                    [
                        'label'         => __('Xóa'),
                        'route'         => route('admin.products.destroy', $product->uuid),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => __('Xóa sản phẩm?'),
                        'confirm_text'  => __('Bạn có chắc chắn muốn xóa sản phẩm ":name"?', ['name' => $name]),
                        'confirm_btn'   => __('Xóa ngay')
                    ],
                ];
            @endphp
            <tr class="group">
                <td class="py-3 px-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $product->id }}" class="row-checkbox form-check-input">
                </td>
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary 
                        :image="$imageUrl" 
                        :title="$name" 
                        :subtitle="$sku" 
                        :actions="$actions" 
                    />
                </td>
                <td class="py-3 px-3 small">
                    <div>
                        <span class="text-body-secondary">{{ __('Danh mục:') }}</span>
                        <span class="fw-medium text-body">{{ $product->category?->translated_name ?? '---' }}</span>
                    </div>
                    <div>
                        <span class="text-body-secondary">{{ __('Thương hiệu:') }}</span>
                        <span class="text-body">{{ $product->brand?->name ?? '---' }}</span>
                    </div>
                </td>
                <td class="py-3 px-3 small">
                    <div class="fw-bold text-primary">
                        {{ number_format($product->price, 0, ',', '.') }} đ
                    </div>
                    @if($product->compare_price && $product->compare_price > $product->price)
                        <div class="text-body-secondary text-decoration-line-through small">
                            {{ number_format($product->compare_price, 0, ',', '.') }} đ
                        </div>
                    @endif
                </td>
                <td class="py-3 px-3 text-center">
                    @if($product->track_inventory)
                        @if($product->stock_quantity > 10)
                            <span class="badge text-bg-success-subtle text-success border border-success-subtle font-monospace px-2 py-1">
                                {{ $product->stock_quantity }} {{ __('trong kho') }}
                            </span>
                        @elseif($product->stock_quantity > 0)
                            <span class="badge text-bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-2 py-1">
                                {{ $product->stock_quantity }} {{ __('sắp hết') }}
                            </span>
                        @else
                            <span class="badge text-bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                {{ __('Hết hàng') }}
                            </span>
                        @endif
                    @else
                        <span class="text-body-secondary small">{{ __('Không theo dõi') }}</span>
                    @endif
                </td>
                <td class="py-3 px-3 text-center">
                    @php
                        $statusBadges = [
                            'published' => 'text-bg-success-subtle text-success border border-success-subtle',
                            'draft'     => 'text-bg-secondary-subtle text-secondary',
                            'archived'  => 'text-bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                        ];
                        $badgeClass = $statusBadges[$product->status] ?? 'text-bg-secondary-subtle text-secondary';
                    @endphp
                    <span class="badge {{ $badgeClass }} px-2 py-1">
                        {{ $statuses[$product->status] ?? $product->status }}
                    </span>
                    @if($product->is_featured)
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1" title="{{ __('Sản phẩm nổi bật') }}">
                            <i class="bi bi-star-fill text-warning"></i>
                        </span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-body-secondary">
                    <i class="bi bi-box-seam fs-2 d-block mb-2 text-muted"></i>
                    {{ __('Không tìm thấy sản phẩm nào.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection