@extends('layouts.admin')

@php
    $attribute = $attribute ?? null;
    $isEdit = !is_null($attribute);
@endphp

@section('title', $isEdit ? __('Sửa Thuộc tính Sản phẩm') : __('Thêm Thuộc tính mới'))

@section('content')
    {{-- Page Header --}}
    <x-admin.page-header 
        :title="$isEdit ? __('Sửa Thuộc tính Sản phẩm') : __('Thêm Thuộc tính mới')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Thuộc tính sản phẩm'), 'url' => route('admin.product-attributes.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')]
        ]" 
    />

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
            {{-- CỘT TRÁI: TÊN ĐA NGÔN NGỮ & DANH SÁCH GIÁ TRỊ            --}}
            {{-- ======================================================== --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">
                
                {{-- Tabs chọn ngôn ngữ --}}
                @if(isset($activeLanguages) && $activeLanguages->count() > 1)
                    <x-admin.lang-tabs :active-languages="$activeLanguages" :default-locale="$defaultLocale" />
                @endif

                @foreach($activeLanguages as $lang)
                    @php 
                        $code = $lang->code ?? app()->getLocale(); 
                        $panelClass = ($code === $defaultLocale) ? '' : 'd-none'; 
                        $translation = $attribute?->translate($code);
                    @endphp

                    <div class="lang-panel {{ $panelClass }} d-flex flex-column gap-4" data-lang-panel="{{ $code }}">
                        
                        {{-- CARD: TÊN THUỘC TÍNH --}}
                        <x-admin.card>
                            <x-admin.form-group 
                                :label="__('Tên thuộc tính')" 
                                name="translations.{{ $code }}.name" 
                                :required="$code === $defaultLocale"
                                :description="__('Tên hiển thị với người dùng (Ví dụ: Màu sắc, Kích thước, Dung lượng...)')"
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
                        </x-admin.card>

                    </div>
                @endforeach

                {{-- CARD: DANH SÁCH GIÁ TRỊ THUỘC TÍNH (VALUES REPEATER) --}}
                <x-admin.card :title="__('Các giá trị thuộc tính')">
                    <p class="text-body-secondary small mb-3">
                        {{ __('Nhập danh sách các lựa chọn giá trị cho thuộc tính này (Ví dụ: Đỏ, Xanh, Vàng hoặc S, M, L, XL).') }}
                    </p>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle table-borderless mb-0" id="values-table">
                            <thead>
                                <tr class="border-bottom text-body-secondary small">
                                    <th style="min-width: 200px;">{{ __('Giá trị (Tiêu đề)') }} <span class="text-danger">*</span></th>
                                    <th class="color-col {{ old('type', $attribute?->type ?? 'select') === 'color' ? '' : 'd-none' }}" style="width: 140px;">
                                        {{ __('Mã màu') }}
                                    </th>
                                    <th style="width: 100px;">{{ __('Thứ tự') }}</th>
                                    <th style="width: 50px;" class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody id="values-container">
                                @php
                                    $values = old('values', $attribute ? $attribute->values->toArray() : []);
                                    if (empty($values)) {
                                        $values = [['value' => '', 'color_code' => '#000000', 'display_order' => 0]];
                                    }
                                @endphp

                                @foreach($values as $index => $val)
                                    <tr class="value-row">
                                        <td>
                                            @if(!empty($val['uuid']))
                                                <input type="hidden" name="values[{{ $index }}][uuid]" value="{{ $val['uuid'] }}">
                                            @endif
                                            <input type="text" 
                                                   name="values[{{ $index }}][value]" 
                                                   class="form-control form-control-sm" 
                                                   value="{{ $val['value'] ?? '' }}" 
                                                   placeholder="{{ __('Nhập giá trị...') }}">
                                        </td>
                                        <td class="color-col {{ old('type', $attribute?->type ?? 'select') === 'color' ? '' : 'd-none' }}">
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" 
                                                       class="form-control form-control-color form-control-sm p-1" 
                                                       value="{{ $val['color_code'] ?? '#0d6efd' }}" 
                                                       title="{{ __('Chọn màu') }}"
                                                       onchange="this.nextElementSibling.value = this.value">
                                                <input type="text" 
                                                       name="values[{{ $index }}][color_code]" 
                                                       class="form-control form-control-sm font-monospace text-uppercase" 
                                                       value="{{ $val['color_code'] ?? '' }}" 
                                                       placeholder="#000000"
                                                       style="width: 85px;"
                                                       oninput="this.previousElementSibling.value = this.value">
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   name="values[{{ $index }}][display_order]" 
                                                   class="form-control form-control-sm text-center" 
                                                   value="{{ $val['display_order'] ?? $index }}" 
                                                   min="0">
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="{{ __('Xóa dòng') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1" id="btn-add-value">
                            <i class="bi bi-plus-lg"></i>
                            <span>{{ __('Thêm giá trị') }}</span>
                        </button>
                    </div>
                </x-admin.card>

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

                {{-- HỘP 2: CẤU HÌNH THUỘC TÍNH --}}
                <x-admin.card :title="__('Cấu hình thuộc tính')">
                    {{-- Mã thuộc tính --}}
                    <x-admin.form-group 
                        :label="__('Mã thuộc tính (Code)')" 
                        name="code" 
                        required
                        :description="__('Dùng để định danh duy nhất (Ví dụ: color, size, material).')"
                        class="mb-3"
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

                    {{-- Loại thuộc tính --}}
                    <x-admin.form-group 
                        :label="__('Loại hiển thị')" 
                        name="type" 
                        required
                        class="mb-3"
                    >
                        <select name="type" id="attribute-type-select" class="form-select form-select-sm" required>
                            @foreach($types as $typeKey => $typeLabel)
                                <option value="{{ $typeKey }}" {{ old('type', $attribute?->type ?? 'select') === $typeKey ? 'selected' : '' }}>
                                    {{ $typeLabel }}
                                </option>
                            @endforeach
                        </select>
                    </x-admin.form-group>

                    {{-- Cho phép lọc tìm kiếm --}}
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="is_filterable" 
                               id="is_filterable" 
                               value="1" 
                               {{ old('is_filterable', $attribute?->is_filterable ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-medium" for="is_filterable">
                            {{ __('Dùng làm bộ lọc tìm kiếm sản phẩm') }}
                        </label>
                    </div>

                    {{-- Thứ tự --}}
                    <x-admin.form-group :label="__('Thứ tự sắp xếp')" name="display_order" class="mb-0">
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

    {{-- Template dòng giá trị mới --}}
    <template id="value-row-template">
        <tr class="value-row">
            <td>
                <input type="text" 
                       name="values[__INDEX__][value]" 
                       class="form-control form-control-sm" 
                       placeholder="{{ __('Nhập giá trị...') }}">
            </td>
            <td class="color-col">
                <div class="d-flex align-items-center gap-2">
                    <input type="color" 
                           class="form-control form-control-color form-control-sm p-1" 
                           value="#0d6efd" 
                           title="{{ __('Chọn màu') }}"
                           onchange="this.nextElementSibling.value = this.value">
                    <input type="text" 
                           name="values[__INDEX__][color_code]" 
                           class="form-control form-control-sm font-monospace text-uppercase" 
                           value="" 
                           placeholder="#000000"
                           style="width: 85px;"
                           oninput="this.previousElementSibling.value = this.value">
                </div>
            </td>
            <td>
                <input type="number" 
                       name="values[__INDEX__][display_order]" 
                       class="form-control form-control-sm text-center" 
                       value="__INDEX__" 
                       min="0">
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="{{ __('Xóa dòng') }}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    </template>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var typeSelect = document.getElementById('attribute-type-select');
            var valuesContainer = document.getElementById('values-container');
            var btnAddValue = document.getElementById('btn-add-value');
            var template = document.getElementById('value-row-template').innerHTML;

            function toggleColorCols() {
                var isColor = typeSelect.value === 'color';
                document.querySelectorAll('.color-col').forEach(function (col) {
                    if (isColor) {
                        col.classList.remove('d-none');
                    } else {
                        col.classList.add('d-none');
                    }
                });
            }

            typeSelect.addEventListener('change', toggleColorCols);
            toggleColorCols();

            // Thêm dòng mới
            btnAddValue.addEventListener('click', function () {
                var nextIndex = valuesContainer.querySelectorAll('tr.value-row').length + Date.now();
                var rowHtml = template.replace(/__INDEX__/g, nextIndex);
                valuesContainer.insertAdjacentHTML('beforeend', rowHtml);
                toggleColorCols();
            });

            // Xóa dòng
            valuesContainer.addEventListener('click', function (e) {
                var removeBtn = e.target.closest('.btn-remove-row');
                if (removeBtn) {
                    var row = removeBtn.closest('tr');
                    if (valuesContainer.querySelectorAll('tr.value-row').length > 1) {
                        row.remove();
                    } else {
                        // Reset input nếu chỉ còn 1 dòng
                        row.querySelectorAll('input').forEach(function (input) {
                            if (input.type === 'number') input.value = 0;
                            else if (input.type === 'color') input.value = '#000000';
                            else input.value = '';
                        });
                    }
                }
            });
        });
    </script>
    @endpush
@endsection