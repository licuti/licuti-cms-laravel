@extends('layouts.admin')

@php
    $attribute = $attribute ?? null;
    $isEdit = !is_null($attribute);
@endphp

@section('title', $isEdit ? __('Sửa Thuộc tính: :name', ['name' => $attribute->name]) : __('Thêm Thuộc tính mới'))

@section('content')
    {{-- Page Header --}}
    <x-admin.page-header 
        :title="$isEdit ? __('Sửa Thuộc tính: :name', ['name' => $attribute->name]) : __('Thêm Thuộc tính mới')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Thuộc tính sản phẩm'), 'url' => route('admin.product-attributes.index')],
            ['label' => $isEdit ? $attribute->name : __('Thêm mới')]
        ]" 
    >
        @if($isEdit)
            <x-slot:actions>
                <x-admin.button href="{{ route('admin.product-attributes.values.index', $attribute->uuid) }}" variant="secondary" size="sm" class="flex-shrink-0">
                    <i class="bi bi-list-ul me-1"></i>
                    <span>{{ __('Quản lý giá trị') }} ({{ $attribute->values->count() }})</span>
                </x-admin.button>
                <x-admin.button href="{{ route('admin.product-attributes.values.create', $attribute->uuid) }}" variant="primary" size="sm" class="flex-shrink-0">
                    <i class="bi bi-plus-lg me-1"></i>
                    <span>{{ __('Thêm giá trị') }}</span>
                </x-admin.button>
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    {{-- Main Form --}}
    <form 
        id="attribute-form" 
        action="{{ $isEdit ? route('admin.product-attributes.update', $attribute->uuid) : route('admin.product-attributes.store') }}" 
        method="POST"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row align-items-start g-4">

            {{-- ======================================================== --}}
            {{-- CỘT TRÁI: THÔNG TIN THUỘC TÍNH & DANH SÁCH GIÁ TRỊ       --}}
            {{-- ======================================================== --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">
                
                {{-- CARD 1: THÔNG TIN CỐT LÕI CỦA THUỘC TÍNH --}}
                <x-admin.card :title="__('Thông tin thuộc tính')">
                    {{-- Tabs chọn ngôn ngữ nếu có nhiều hơn 1 ngôn ngữ --}}
                    @if(isset($activeLanguages) && $activeLanguages->count() > 1)
                        <div class="mb-3">
                            <x-admin.lang-tabs :active-languages="$activeLanguages" :default-locale="$defaultLocale" />
                        </div>
                    @endif

                    {{-- Nhập tên thuộc tính theo từng ngôn ngữ --}}
                    @foreach($activeLanguages as $lang)
                        @php 
                            $code = $lang->code ?? app()->getLocale(); 
                            $panelClass = ($code === $defaultLocale) ? '' : 'd-none'; 
                            $translation = $attribute?->translate($code);
                        @endphp

                        <div class="lang-panel {{ $panelClass }}" data-lang-panel="{{ $code }}">
                            <x-admin.form-group 
                                :label="__('Tên thuộc tính')" 
                                name="translations.{{ $code }}.name" 
                                :required="$code === $defaultLocale"
                                :description="__('Tên hiển thị với người dùng (Ví dụ: Màu sắc, Kích thước, Dung lượng...)')"
                                class="mb-3"
                            >
                                <x-admin.input 
                                    type="text" 
                                    name="translations[{{ $code }}][name]" 
                                    size="sm"
                                    value="{{ old('translations.'.$code.'.name', $translation?->name ?? '') }}" 
                                    placeholder="{{ __('Ví dụ: Màu sắc, Kích cỡ...') }}" 
                                    :required="$code === $defaultLocale"
                                />
                            </x-admin.form-group>
                        </div>
                    @endforeach

                    {{-- Cấu hình kỹ thuật (Mã code & Loại hiển thị) --}}
                    <div class="row g-3 pt-3 border-top">
                        <div class="col-md-6">
                            <x-admin.form-group 
                                :label="__('Mã thuộc tính (Code)')" 
                                name="code" 
                                required
                                :description="__('Dùng để định danh duy nhất (Ví dụ: color, size, material).')"
                                class="mb-0"
                            >
                                <x-admin.input 
                                    type="text" 
                                    name="code" 
                                    size="sm"
                                    value="{{ old('code', $attribute?->code ?? '') }}" 
                                    placeholder="color" 
                                    required
                                />
                            </x-admin.form-group>
                        </div>

                        <div class="col-md-6">
                            <x-admin.form-group 
                                :label="__('Loại hiển thị')" 
                                name="type" 
                                required
                                :description="__('Cách hiển thị tùy chọn cho khách hàng.')"
                                class="mb-0"
                            >
                                <x-admin.select 
                                    name="type" 
                                    id="attribute-type-select" 
                                    size="sm"
                                    :options="$types"
                                    :value="old('type', $attribute?->type ?? \App\Enums\AttributeType::SELECT->value)"
                                    required
                                />
                            </x-admin.form-group>
                        </div>
                    </div>
                </x-admin.card>

                {{-- CARD 2: DANH SÁCH GIÁ TRỊ (KHI ĐANG SỬA THUỘC TÍNH) --}}
                @if($isEdit)
                    <x-admin.card :title="__('Giá trị thuộc tính')">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-body-secondary small">
                                {{ __('Đang có') }}: <strong>{{ $attribute->values->count() }}</strong> {{ __('giá trị') }}
                            </span>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.product-attributes.values.index', $attribute->uuid) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> {{ __('Xem tất cả') }}
                                </a>
                                <a href="{{ route('admin.product-attributes.values.create', $attribute->uuid) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i> {{ __('Thêm giá trị mới') }}
                                </a>
                            </div>
                        </div>

                        @if($attribute->values->count() > 0)
                            <div class="table-responsive border rounded-2">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="py-2 px-3">{{ __('Giá trị') }}</th>
                                            @if($attribute->type === \App\Enums\AttributeType::COLOR->value)
                                                <th class="py-2 px-3">{{ __('Mã màu') }}</th>
                                            @endif
                                            <th class="py-2 px-3 text-center" style="width: 100px;">{{ __('Thứ tự') }}</th>
                                            <th class="py-2 px-3 text-end" style="width: 120px;">{{ __('Thao tác') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($attribute->values as $attrValue)
                                            <tr>
                                                <td class="py-2 px-3 fw-medium">
                                                    @if($attribute->type === \App\Enums\AttributeType::COLOR->value && $attrValue->color_code)
                                                        <span class="rounded-circle d-inline-block border align-middle me-1" style="width: 14px; height: 14px; background-color: {{ $attrValue->color_code }};"></span>
                                                    @endif
                                                    {{ $attrValue->value }}
                                                </td>
                                                @if($attribute->type === \App\Enums\AttributeType::COLOR->value)
                                                    <td class="py-2 px-3 font-monospace text-body-secondary">
                                                        {{ $attrValue->color_code ?: '---' }}
                                                    </td>
                                                @endif
                                                <td class="py-2 px-3 text-center text-body-secondary">
                                                    {{ $attrValue->display_order }}
                                                </td>
                                                <td class="py-2 px-3 text-end">
                                                    <a href="{{ route('admin.product-attributes.values.edit', [$attribute->uuid, $attrValue->uuid]) }}" class="btn btn-link link-primary p-0 text-decoration-none me-2">
                                                        {{ __('Sửa') }}
                                                    </a>
                                                    <form action="{{ route('admin.product-attributes.values.destroy', [$attribute->uuid, $attrValue->uuid]) }}" method="POST" class="d-inline form-confirm" data-confirm-title="{{ __('Xóa giá trị?') }}" data-confirm-text="{{ __('Bạn có chắc chắn muốn xóa giá trị này?') }}" data-confirm-btn="{{ __('Xóa') }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-link link-danger p-0 text-decoration-none">
                                                            {{ __('Xóa') }}
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4 bg-light rounded-2 border border-dashed">
                                <i class="bi bi-tags fs-2 text-muted mb-2 d-block"></i>
                                <p class="text-muted small mb-2">{{ __('Thuộc tính này chưa có giá trị nào.') }}</p>
                                <a href="{{ route('admin.product-attributes.values.create', $attribute->uuid) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i> {{ __('Thêm giá trị đầu tiên') }}
                                </a>
                            </div>
                        @endif
                    </x-admin.card>
                @else
                    {{-- GỢI Ý KHI TẠO MỚI --}}
                    <div class="alert alert-info d-flex align-items-center gap-2 mb-0 py-2 px-3 small border-0 bg-info-subtle text-info-emphasis">
                        <i class="bi bi-info-circle fs-5"></i>
                        <div>
                            {{ __('Bạn có thể thêm danh sách các giá trị (ví dụ: Đỏ, Xanh, Vàng, S, M, L...) sau khi lưu tạo mới thuộc tính này.') }}
                        </div>
                    </div>
                @endif

            </div>

            {{-- ======================================================== --}}
            {{-- CỘT PHẢI: CÀI ĐẶT THUỘC TÍNH (SIDEBAR)                   --}}
            {{-- ======================================================== --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- HỘP 1: THAO TÁC / LƯU --}}
                <x-admin.card :title="__('Thao tác')">
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" name="submit_action" value="save" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-check-circle me-1"></i> {{ $isEdit ? __('Cập nhật') : __('Lưu thuộc tính') }}
                        </button>
                        <button type="submit" name="submit_action" value="save_and_edit" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-pencil-square me-1"></i> {{ __('Lưu & tiếp tục sửa') }}
                        </button>
                        <a href="{{ route('admin.product-attributes.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                            {{ __('Quay lại') }}
                        </a>
                    </div>
                </x-admin.card>

                {{-- HỘP 2: CÀI ĐẶT HIỂN THỊ --}}
                <x-admin.card :title="__('Cài đặt hiển thị')">
                    {{-- Cho phép lọc tìm kiếm --}}
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="is_filterable" value="0">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="is_filterable" 
                               id="is_filterable" 
                               value="1" 
                               {{ old('is_filterable', $attribute?->is_filterable ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-medium" for="is_filterable">
                            {{ __('Dùng làm bộ lọc tìm kiếm') }}
                        </label>
                        <div class="form-text small text-muted">
                            {{ __('Hiển thị trong bộ lọc tìm kiếm sản phẩm.') }}
                        </div>
                    </div>

                    {{-- Thứ tự sắp xếp --}}
                    <x-admin.form-group 
                        :label="__('Thứ tự sắp xếp')" 
                        name="display_order" 
                        :description="__('Số nhỏ hơn sẽ hiển thị trước.')"
                        class="mb-0"
                    >
                        <x-admin.input 
                            type="number" 
                            name="display_order" 
                            size="sm"
                            value="{{ old('display_order', $attribute?->display_order ?? 0) }}" 
                            min="0"
                        />
                    </x-admin.form-group>
                </x-admin.card>

            </div>
        </div>
    </form>
@endsection