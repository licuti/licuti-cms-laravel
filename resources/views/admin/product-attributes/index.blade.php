@extends('layouts.admin')
@section('title', __('Quản lý Thuộc tính Sản phẩm'))

@section('content')
    {{-- Page Header --}}
    <x-admin.page-header 
        :title="__('Thuộc tính Sản phẩm')" 
        :subtitle="__('Quản lý các thuộc tính dùng để tạo biến thể hoặc lọc sản phẩm (Màu sắc, Kích thước, Chất liệu...)')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Thuộc tính sản phẩm')]
        ]">
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.product-attributes.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>{{ __('Thêm thuộc tính') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Search & Filter Bar --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <form action="{{ route('admin.product-attributes.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <x-admin.input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="{{ __('Tìm tên hoặc mã thuộc tính...') }}" 
                size="sm" 
                style="min-width:280px;" 
            />
            
            <x-admin.select name="type" class="w-auto" size="sm">
                <option value="">{{ __('Tất cả loại') }}</option>
                @foreach($types as $typeKey => $typeLabel)
                    <option value="{{ $typeKey }}" {{ request('type') === $typeKey ? 'selected' : '' }}>
                        {{ $typeLabel }}
                    </option>
                @endforeach
            </x-admin.select>
            
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('Lọc') }}</x-admin.button>
            @if(request()->filled('search') || request()->filled('type'))
                <x-admin.button href="{{ route('admin.product-attributes.index') }}" variant="secondary" size="sm">{{ __('Đặt lại') }}</x-admin.button>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <x-admin.table :paginator="$attributes">
        <x-slot:head>
            <x-admin.table-th>{{ __('Tên thuộc tính') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Mã (Code)') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Loại hiển thị') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Giá trị thiết lập') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Lọc tìm kiếm') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Thứ tự') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($attributes as $attribute)
            @php
                $name = $attribute->name;
                $actions = [
                    ['label' => __('Sửa'), 'route' => route('admin.product-attributes.edit', $attribute->uuid), 'color' => 'blue'],
                    [
                        'label'         => __('Xóa'),
                        'route'         => route('admin.product-attributes.destroy', $attribute->uuid),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => __('Xóa thuộc tính?'),
                        'confirm_text'  => __('Bạn có chắc chắn muốn xóa thuộc tính ":name"? Toàn bộ giá trị thuộc tính này sẽ bị xóa.', ['name' => $name]),
                        'confirm_btn'   => __('Xóa ngay')
                    ],
                ];
            @endphp
            <tr class="group">
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary 
                        :title="$name" 
                        :actions="$actions" 
                    />
                </td>
                <td class="py-3 px-3">
                    <code>{{ $attribute->code }}</code>
                </td>
                <td class="py-3 px-3">
                    @php
                        $badgeClasses = [
                            'color'  => 'bg-danger-subtle text-danger border border-danger-subtle',
                            'select' => 'bg-primary-subtle text-primary border border-primary-subtle',
                            'button' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
                            'radio'  => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                        ];
                        $badgeClass = $badgeClasses[$attribute->type] ?? 'bg-secondary-subtle text-secondary';
                    @endphp
                    <span class="badge {{ $badgeClass }} px-2 py-1">
                        {{ $types[$attribute->type] ?? $attribute->type }}
                    </span>
                </td>
                <td class="py-3 px-3">
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                        @forelse($attribute->values->take(6) as $val)
                            <span class="badge text-bg-light border d-inline-flex align-items-center gap-1 font-monospace small">
                                @if($attribute->type === 'color' && $val->color_code)
                                    <span class="rounded-circle d-inline-block border" style="width: 10px; height: 10px; background-color: {{ $val->color_code }};"></span>
                                @endif
                                {{ $val->value }}
                            </span>
                        @empty
                            <span class="text-body-secondary fst-italic small">{{ __('Chưa có giá trị') }}</span>
                        @endforelse

                        @if($attribute->values->count() > 6)
                            <span class="badge text-bg-secondary small">+{{ $attribute->values->count() - 6 }}</span>
                        @endif
                    </div>
                </td>
                <td class="py-3 px-3 text-center">
                    @if($attribute->is_filterable)
                        <span class="badge text-bg-success-subtle text-success border border-success-subtle px-2 py-1">
                            <i class="bi bi-check-lg"></i> {{ __('Cho phép') }}
                        </span>
                    @else
                        <span class="badge text-bg-secondary-subtle text-secondary px-2 py-1">
                            {{ __('Không') }}
                        </span>
                    @endif
                </td>
                <td class="py-3 px-3 text-center text-body-secondary small">
                    {{ $attribute->display_order }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-body-secondary">
                    <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                    {{ __('Không tìm thấy thuộc tính sản phẩm nào.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection