@extends('layouts.admin')
@section('title', __('Quản lý Giá trị thuộc tính'))

@section('content')
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

    {{-- Search & Filter Bar --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <form action="{{ route('admin.product-attributes.values.index', $attribute->uuid) }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
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

    {{-- Table --}}
    <x-admin.table :paginator="$values">
        <x-slot:head>
            <x-admin.table-th>{{ __('Giá trị') }}</x-admin.table-th>
            @if($attribute->type === \App\Enums\AttributeType::COLOR->value)
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
            @endphp
            <tr>
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary 
                        :title="$name" 
                        :actions="$actions" 
                    />
                </td>
                @if($attribute->type === \App\Enums\AttributeType::COLOR->value)
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
                    {{ $valueModel->display_order }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $attribute->type === \App\Enums\AttributeType::COLOR->value ? 4 : 3 }}" class="text-center py-5 text-body-secondary">
                    <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                    {{ __('Chưa có giá trị nào.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection
