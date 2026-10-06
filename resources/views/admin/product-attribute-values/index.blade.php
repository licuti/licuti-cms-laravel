@extends('layouts.admin')
@section('title', __('Quản lý Giá trị thuộc tính'))

@section('content')
    @php
        $isColor = $attribute->type === \App\Enums\AttributeType::COLOR->value;
        // checkbox + giá trị + (màu?) + thứ tự
        $emptyColspan = $isColor ? 4 : 3;
    @endphp

    {{-- Page Header --}}
    <x-admin.page-header 
        :title="__('Giá trị thuộc tính: :name', ['name' => $attribute->name])" 
        :subtitle="__('Quản lý các giá trị cho thuộc tính này')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Thuộc tính sản phẩm'), 'url' => route('admin.product-attributes.index')],
            ['label' => $attribute->name]
        ]">
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.product-attributes.values.create', $attribute->uuid) }}" variant="primary" class="flex-shrink-0">
                <i class="bi bi-plus-lg"></i>
                <span>{{ __('Thêm giá trị') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

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
            <x-admin.button type="button" id="btn-apply-bulk" variant="primary" class="flex-shrink-0" size="sm">
                {{ __('Áp dụng') }}
            </x-admin.button>
        </div>

        {{-- Search & Filter Form --}}
        <form action="{{ route('admin.product-attributes.values.index', $attribute->uuid) }}" method="GET" class="d-flex align-items-center gap-2">
            <x-admin.input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="{{ __('Tìm giá trị...') }}" 
                size="sm" 
            />
            
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('Lọc') }}</x-admin.button>
            @if(request()->filled('search'))
                <x-admin.button href="{{ route('admin.product-attributes.values.index', $attribute->uuid) }}" variant="secondary" size="sm">{{ __('Đặt lại') }}</x-admin.button>
            @endif
        </form>
    </div>

    {{-- Hidden Bulk Action Form --}}
    <form id="form-bulk-action" action="{{ route('admin.product-attributes.values.bulk', $attribute->uuid) }}" method="POST">
        @csrf
        <input type="hidden" name="bulk_module" value="product_attribute_values">
        <input type="hidden" name="action" id="bulk-action-input" value="">
    </form>

    {{-- Table --}}
    <x-admin.table :paginator="$values">
        <x-slot:head>
            <x-admin.table-th align="center" style="width: 40px;">
                <input type="checkbox" id="check-all" class="form-check-input m-0">
            </x-admin.table-th>
            <x-admin.table-th>{{ __('Giá trị') }}</x-admin.table-th>
            @if($isColor)
                <x-admin.table-th>{{ __('Mã màu') }}</x-admin.table-th>
            @endif
            <x-admin.table-th align="center">{{ __('Thứ tự') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($values as $valueModel)
            @php
                $name = $valueModel->value;
                $actions = [
                    ['label' => __('Sửa'), 'route' => route('admin.product-attributes.values.edit', [$attribute->uuid, $valueModel->uuid]), 'color' => 'blue'],
                    [
                        'label'         => __('Xóa'),
                        'route'         => route('admin.product-attributes.values.destroy', [$attribute->uuid, $valueModel->uuid]),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => __('Xóa giá trị?'),
                        'confirm_text'  => __('Bạn có chắc chắn muốn xóa giá trị ":name"?', ['name' => $name]),
                        'confirm_btn'   => __('Xóa ngay')
                    ],
                ];
                $isFirst = $loop->first;
                $isLast = $loop->last;
            @endphp
            <tr>
                <td class="py-3 px-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $valueModel->uuid }}" class="row-checkbox form-check-input m-0">
                </td>
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary 
                        :title="$name" 
                        :actions="$actions" 
                    />
                </td>
                @if($isColor)
                    <td class="py-3 px-3">
                        @if($valueModel->color_code)
                            <div class="d-flex align-items-center gap-2">
                                <span class="rounded-circle d-inline-block border" style="width: 16px; height: 16px; background-color: {{ $valueModel->color_code }};"></span>
                                <code>{{ $valueModel->color_code }}</code>
                            </div>
                        @else
                            <span class="text-body-secondary">---</span>
                        @endif
                    </td>
                @endif
                <td class="py-3 px-3 text-center text-body-secondary small">
                    <div class="d-inline-flex align-items-center gap-1">
                        <span class="d-inline-flex" style="min-width: 3rem;">
                            @if(!$isFirst)
                                <form action="{{ route('admin.product-attributes.values.move_up', [$attribute->uuid, $valueModel->uuid]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <x-admin.button type="submit" variant="link" size="sm"
                                                    class="p-0 lh-1 text-body-secondary align-baseline"
                                                    title="{{ __('Di chuyển lên') }}" aria-label="{{ __('Di chuyển lên') }}">
                                        <i class="bi bi-arrow-up"></i>
                                    </x-admin.button>
                                </form>
                            @endif
                            @if(!$isLast)
                                <form action="{{ route('admin.product-attributes.values.move_down', [$attribute->uuid, $valueModel->uuid]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <x-admin.button type="submit" variant="link" size="sm"
                                                    class="p-0 lh-1 text-body-secondary align-baseline"
                                                    title="{{ __('Di chuyển xuống') }}" aria-label="{{ __('Di chuyển xuống') }}">
                                        <i class="bi bi-arrow-down"></i>
                                    </x-admin.button>
                                </form>
                            @endif
                        </span>
                        <span class="ms-1">{{ $valueModel->display_order }}</span>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $emptyColspan }}" class="text-center py-5 text-body-secondary">
                    <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                    {{ request()->filled('search')
                        ? __('Không tìm thấy giá trị nào phù hợp với từ khóa.')
                        : __('Chưa có giá trị nào.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection
