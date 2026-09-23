@extends('layouts.admin')

@php
    $menu = $menu ?? null;
    $isEdit = !is_null($menu);
@endphp

@section('title', $isEdit ? __('Sửa Menu') : __('Thêm Menu mới'))

@section('content')
    {{-- Page Header --}}
    <x-admin.page-header 
        :title="$isEdit ? __('Sửa Menu') : __('Thêm Menu mới')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Menu & Điều hướng'), 'url' => route('admin.menus.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')]
        ]" 
    />

    {{-- Main Form --}}
    <form 
        id="menu-form" 
        action="{{ $isEdit ? route('admin.menus.update', $menu->uuid) : route('admin.menus.store') }}" 
        method="POST"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row align-items-start g-4">

            {{-- ======================================================== --}}
            {{-- CỘT TRÁI: THÔNG TIN MENU & CÁC MỤC LIÊN KẾT              --}}
            {{-- ======================================================== --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">
                
                {{-- CARD 1: THÔNG TIN CƠ BẢN --}}
                <x-admin.card :title="__('Thông tin Menu')">
                    {{-- Tên menu --}}
                    <x-admin.form-group 
                        :label="__('Tên menu')" 
                        name="name" 
                        required
                        :description="__('Tên phân biệt các menu trong quản trị (Ví dụ: Menu chính, Menu chân trang, Menu hỗ trợ)')"
                        class="mb-3"
                    >
                        <x-admin.input 
                            type="text" 
                            name="name" 
                            size="sm"
                            value="{{ old('name', $menu?->name ?? '') }}" 
                            placeholder="{{ __('Ví dụ: Menu chính') }}" 
                            class="seo-source-name"
                            required
                        />
                    </x-admin.form-group>

                    {{-- Slug --}}
                    <x-admin.form-group 
                        :label="__('Mã định danh (Slug)')" 
                        name="slug"
                        :description="__('Định danh duy nhất dùng để gọi menu ngoài giao diện theme.')"
                        class="mb-0"
                    >
                        <x-admin.input 
                            type="text" 
                            name="slug" 
                            size="sm" 
                            value="{{ old('slug', $menu?->slug ?? '') }}" 
                            placeholder="main-menu" 
                            class="seo-source-slug" 
                        />
                    </x-admin.form-group>
                </x-admin.card>

                {{-- CARD 2: CÁC MỤC LIÊN KẾT (MENU ITEMS) --}}
                <x-admin.card :title="__('Danh sách liên kết trong menu')">
                    <p class="text-body-secondary small mb-3">
                        {{ __('Thiết lập các liên kết điều hướng trên thanh menu này.') }}
                    </p>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle table-borderless mb-0" id="items-table">
                            <thead>
                                <tr class="border-bottom text-body-secondary small">
                                    <th style="min-width: 180px;">{{ __('Tiêu đề liên kết') }} <span class="text-danger">*</span></th>
                                    <th style="min-width: 200px;">{{ __('Đường dẫn URL') }} <span class="text-danger">*</span></th>
                                    <th style="width: 100px;" class="text-center">{{ __('Tab mới') }}</th>
                                    <th style="width: 80px;" class="text-center">{{ __('Thứ tự') }}</th>
                                    <th style="width: 40px;" class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody id="items-container">
                                @php
                                    $items = old('items', $menu ? $menu->items->toArray() : []);
                                    if (empty($items)) {
                                        $items = [
                                            ['title' => 'Trang chủ', 'url' => '/', 'target' => '_self', 'display_order' => 0],
                                            ['title' => 'Sản phẩm', 'url' => '/products', 'target' => '_self', 'display_order' => 1],
                                            ['title' => 'Tin tức', 'url' => '/posts', 'target' => '_self', 'display_order' => 2],
                                            ['title' => 'Liên hệ', 'url' => '/contact', 'target' => '_self', 'display_order' => 3],
                                        ];
                                    }
                                @endphp

                                @foreach($items as $index => $item)
                                    <tr class="item-row">
                                        <td>
                                            @if(!empty($item['uuid']))
                                                <input type="hidden" name="items[{{ $index }}][uuid]" value="{{ $item['uuid'] }}">
                                            @endif
                                            <input type="text" 
                                                   name="items[{{ $index }}][title]" 
                                                   class="form-control form-control-sm" 
                                                   value="{{ $item['title'] ?? '' }}" 
                                                   placeholder="{{ __('Tên liên kết...') }}" 
                                                   required>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   name="items[{{ $index }}][url]" 
                                                   class="form-control form-control-sm font-monospace" 
                                                   value="{{ $item['url'] ?? '/' }}" 
                                                   placeholder="/" 
                                                   required>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="items[{{ $index }}][target]" 
                                                   value="_blank" 
                                                   {{ ($item['target'] ?? '_self') === '_blank' ? 'checked' : '' }} 
                                                   title="{{ __('Mở tab mới') }}">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   name="items[{{ $index }}][display_order]" 
                                                   class="form-control form-control-sm text-center" 
                                                   value="{{ $item['display_order'] ?? $index }}" 
                                                   min="0">
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" title="{{ __('Xóa liên kết') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-1" id="btn-add-item">
                            <i class="bi bi-plus-lg"></i>
                            <span>{{ __('Thêm mục liên kết') }}</span>
                        </button>
                    </div>
                </x-admin.card>

            </div>

            {{-- ======================================================== --}}
            {{-- CỘT PHẢI: CÀI ĐẶT VỊ TRÍ & TRẠNG THÁI (SIDEBAR)          --}}
            {{-- ======================================================== --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- HỘP 1: THAO TÁC / LƯU --}}
                <x-admin.card :title="__('Thao tác')">
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" name="submit_action" value="save" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-check-circle me-1"></i> {{ $isEdit ? __('Cập nhật') : __('Lưu menu') }}
                        </button>
                        <button type="submit" name="submit_action" value="save_and_edit" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-pencil-square me-1"></i> {{ __('Lưu & tiếp tục sửa') }}
                        </button>
                        <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                            {{ __('Quay lại') }}
                        </a>
                    </div>
                </x-admin.card>

                {{-- HỘP 2: CẤU HÌNH VỊ TRÍ --}}
                <x-admin.card :title="__('Cấu hình hiển thị')">
                    <x-admin.form-group :label="__('Vị trí hiển thị trên giao diện')" name="location" class="mb-3">
                        <select name="location" class="form-select form-select-sm">
                            <option value="">{{ __('--- Không gán vị trí ---') }}</option>
                            @foreach($locations as $locKey => $locLabel)
                                <option value="{{ $locKey }}" {{ old('location', $menu?->location ?? '') === $locKey ? 'selected' : '' }}>
                                    {{ $locLabel }}
                                </option>
                            @endforeach
                        </select>
                    </x-admin.form-group>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="is_active" 
                               id="is_active" 
                               value="1" 
                               {{ old('is_active', $menu?->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-medium" for="is_active">
                            {{ __('Kích hoạt menu này') }}
                        </label>
                    </div>
                </x-admin.card>

            </div>
        </div>
    </form>

    <template id="item-row-template">
        <tr class="item-row">
            <td>
                <input type="text" 
                       name="items[__INDEX__][title]" 
                       class="form-control form-control-sm" 
                       placeholder="{{ __('Tên liên kết...') }}" 
                       required>
            </td>
            <td>
                <input type="text" 
                       name="items[__INDEX__][url]" 
                       class="form-control form-control-sm font-monospace" 
                       value="/" 
                       placeholder="/" 
                       required>
            </td>
            <td class="text-center">
                <input class="form-check-input" 
                       type="checkbox" 
                       name="items[__INDEX__][target]" 
                       value="_blank" 
                       title="{{ __('Mở tab mới') }}">
            </td>
            <td>
                <input type="number" 
                       name="items[__INDEX__][display_order]" 
                       class="form-control form-control-sm text-center" 
                       value="__INDEX__" 
                       min="0">
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" title="{{ __('Xóa liên kết') }}">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    </template>

    <x-admin.scripts.auto-slug :is-edit="$isEdit" />

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var itemsContainer = document.getElementById('items-container');
            var btnAddItem = document.getElementById('btn-add-item');
            var template = document.getElementById('item-row-template').innerHTML;

            // Thêm dòng mới (index tăng dần, không dùng Date.now() để tránh tràn display_order)
            var nextIndex = 0;
            itemsContainer.querySelectorAll('tr.item-row').forEach(function (row) {
                var orderInput = row.querySelector('input[name$="[display_order]"]');
                if (orderInput) {
                    nextIndex = Math.max(nextIndex, parseInt(orderInput.value || '0', 10) + 1);
                }
            });

            btnAddItem.addEventListener('click', function () {
                var rowHtml = template.replace(/__INDEX__/g, nextIndex++);
                itemsContainer.insertAdjacentHTML('beforeend', rowHtml);
            });

            itemsContainer.addEventListener('click', function (e) {
                var removeBtn = e.target.closest('.btn-remove-item');
                if (removeBtn) {
                    var row = removeBtn.closest('tr');
                    if (itemsContainer.querySelectorAll('tr.item-row').length > 1) {
                        row.remove();
                    } else {
                        row.querySelectorAll('input[type="text"]').forEach(function(i){ i.value = ''; });
                    }
                }
            });
        });
    </script>
    @endpush
@endsection