@props([
    'product'      => null,
    'defaultPrice' => 0,
    'defaultStock' => 0,
])

@php
    $wrapperId = 'product_variants_' . uniqid();

    // Render sẵn các variant hiện có (edit mode) để dữ liệu không mất nếu JS chưa chạy
    $rows = [];

    if ($product) {
        foreach ($product->variants as $variant) {
            $valueIds = $variant->attributeValues->pluck('id')->all();
            sort($valueIds, SORT_NUMERIC);
            $key = implode('-', $valueIds);

            $rows[] = [
                'key'           => $key,
                'name'          => $variant->attributeValues->pluck('value')->implode(' - '),
                'sku'           => $variant->sku,
                'barcode'       => $variant->barcode,
                'price'         => $variant->price !== null ? (float) $variant->price : null,
                'compare_price' => $variant->compare_price !== null ? (float) $variant->compare_price : null,
                'cost_price'    => $variant->cost_price !== null ? (float) $variant->cost_price : null,
                'stock'         => (int) $variant->stock_quantity,
                'image'         => $variant->image,
                'image_url'     => $variant->image_url,
                'is_active'     => (bool) $variant->is_active,
            ];
        }
    }
@endphp

<div class="product-variants-wrapper" id="{{ $wrapperId }}"
     data-default-price="{{ (float) $defaultPrice }}"
     data-default-stock="{{ (int) $defaultStock }}">

    <p class="text-body-secondary small mb-3">
        {{ __('Tự sinh từ tổ hợp các giá trị đã chọn ở các thuộc tính được bật "Dùng làm trục biến thể". Mỗi lần thêm/bỏ giá trị, bảng cập nhật và giữ nguyên dữ liệu đã nhập của tổ hợp còn hợp lệ.') }}
    </p>

    {{-- Toolbar hàng loạt --}}
    <div class="d-flex flex-wrap gap-2 mb-3 variants-toolbar">
        <button type="button" class="btn btn-outline-secondary btn-sm btn-generate-skus">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span class="ms-1">{{ __('Sinh SKU tự động') }}</span>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm btn-apply-price">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="ms-1">{{ __('Áp dụng giá cho tất cả') }}</span>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm btn-apply-stock">
            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <span class="ms-1">{{ __('Áp dụng tồn kho cho tất cả') }}</span>
        </button>
    </div>

    <div class="variants-table-wrapper border rounded" style="max-height: 480px; overflow-y: auto;">
        <table class="table table-sm align-middle table-borderless mb-0 variants-table">
            <thead class="sticky-top bg-body">
                <tr class="border-bottom text-body-secondary small">
                    <th>{{ __('Biến thể') }}</th>
                    <th style="width: 150px;">{{ __('Mã SKU') }}</th>
                    <th style="width: 120px;">{{ __('Giá (đ)') }}</th>
                    <th style="width: 100px;">{{ __('Tồn kho') }}</th>
                    <th style="width: 60px;" class="text-center">{{ __('Hoạt động') }}</th>
                    <th style="width: 40px;"></th>
                </tr>
            </thead>
            <tbody class="variants-body">
                @foreach ($rows as $row)
                    <tr class="variant-row" data-key="{{ $row['key'] }}">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $row['image_url'] }}" alt="" class="variant-thumb rounded border @if (empty($row['image_url'])) d-none @endif" style="width: 32px; height: 32px; object-fit: cover;">
                                <span class="fw-medium">{{ $row['name'] }}</span>
                            </div>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm variant-sku" name="variants[{{ $row['key'] }}][sku]" value="{{ $row['sku'] }}" placeholder="{{ __('SKU') }}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm variant-price" name="variants[{{ $row['key'] }}][price]" value="{{ $row['price'] }}" min="0" step="1000" placeholder="{{ $defaultPrice }}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm variant-stock" name="variants[{{ $row['key'] }}][stock_quantity]" value="{{ $row['stock'] }}" min="0">
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-inline-block mb-0">
                                @php $activeId = 'variant_active_' . $row['key'] . '_' . uniqid(); @endphp
                                <input class="form-check-input" type="checkbox" role="switch" id="{{ $activeId }}" name="variants[{{ $row['key'] }}][is_active]" value="1" @checked($row['is_active'])>
                                <label class="form-check-label" for="{{ $activeId }}"></label>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-secondary btn-variant-detail" title="{{ __('Chi tiết biến thể') }}">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </button>
                        </td>

                        {{-- Hidden fields: trường phụ từ modal chi tiết --}}
                        <input type="hidden" class="variant-compare_price" name="variants[{{ $row['key'] }}][compare_price]" value="{{ $row['compare_price'] }}">
                        <input type="hidden" class="variant-cost_price" name="variants[{{ $row['key'] }}][cost_price]" value="{{ $row['cost_price'] }}">
                        <input type="hidden" class="variant-barcode" name="variants[{{ $row['key'] }}][barcode]" value="{{ $row['barcode'] }}">
                        <input type="hidden" class="variant-image-uuid" name="variants[{{ $row['key'] }}][image_uuid]" value="{{ $row['image'] }}">
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="variants-empty text-body-secondary small mb-0 mt-2 @if (!empty($rows)) d-none @endif">
        {{ __('Chưa có biến thể. Đánh dấu "Dùng làm trục biến thể" ở các thuộc tính và chọn giá trị để hệ thống tự sinh tổ hợp.') }}
    </p>

    <div class="variants-warning text-warning small mt-2 d-none" role="alert"></div>

    {{-- Modal chi tiết biến thể --}}
    <x-admin.modal id="{{ $wrapperId }}_detail" :title="__('Chi tiết biến thể')" max-width="modal-lg">
        <div class="row g-3">
            <div class="col-md-6">
                <x-admin.form-group :label="__('Giá so sánh (đ)')" name="detail_compare_price" class="mb-0">
                    <x-admin.input type="number" name="detail_compare_price" size="sm" min="0" step="1000" class="detail-field" data-field="compare_price" />
                </x-admin.form-group>
            </div>
            <div class="col-md-6">
                <x-admin.form-group :label="__('Giá vốn (đ)')" name="detail_cost_price" class="mb-0">
                    <x-admin.input type="number" name="detail_cost_price" size="sm" min="0" step="1000" class="detail-field" data-field="cost_price" />
                </x-admin.form-group>
            </div>
            <div class="col-md-6">
                <x-admin.form-group :label="__('Mã vạch (Barcode)')" name="detail_barcode" class="mb-0">
                    <x-admin.input type="text" name="detail_barcode" size="sm" class="detail-field" data-field="barcode" placeholder="893..." />
                </x-admin.form-group>
            </div>
            <div class="col-md-6">
                <x-admin.form-group :label="__('Ảnh biến thể')" name="detail_image" class="mb-0">
                    <x-admin.image-upload name="detail_image" shape="square" class="detail-image-upload" />
                </x-admin.form-group>
            </div>
        </div>

        @slot('footer')
            <button type="button" class="btn btn-primary btn-sm btn-save-variant-detail">{{ __('Lưu chi tiết') }}</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('Đóng') }}</button>
        @endslot
    </x-admin.modal>

    {{-- Template dòng biến thể mới (clone bằng JS) --}}
    <template class="variant-row-template">
        <tr class="variant-row" data-key="__KEY__">
            <td>
                <div class="d-flex align-items-center gap-2">
                    <img alt="" class="variant-thumb rounded border d-none" style="width: 32px; height: 32px; object-fit: cover;">
                    <span class="fw-medium variant-name">__NAME__</span>
                </div>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm variant-sku" name="variants[__KEY__][sku]" placeholder="{{ __('SKU') }}">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm variant-price" name="variants[__KEY__][price]" min="0" step="1000" placeholder="__DEFAULT_PRICE__">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm variant-stock" name="variants[__KEY__][stock_quantity]" min="0" value="__DEFAULT_STOCK__">
            </td>
            <td class="text-center">
                <div class="form-check form-switch d-inline-block mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="__ACTIVE_ID__" name="variants[__KEY__][is_active]" value="1" checked>
                    <label class="form-check-label" for="__ACTIVE_ID__"></label>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-secondary btn-variant-detail" title="{{ __('Chi tiết biến thể') }}">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </button>
            </td>
        </tr>
    </template>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var MAX_COMBOS = {{ (int) config('products.max_combos', 100) }};

    document.querySelectorAll('.product-attributes-wrapper').forEach(function (attributesWrapper) {
        attributesWrapper.addEventListener('product-attributes:change', function (e) {
            renderVariants(e.detail.matrix);
        });
    });

    function findVariantsWrapper() {
        return document.querySelector('.product-variants-wrapper');
    }

    // Bộ nhớ các giá trị đã nhập theo combo key — NẰM NGOÀI renderVariants
    // để tổ hợp bị bỏ rồi tạo lại vẫn khôi phục được dữ liệu cũ.
    var enteredValues = {};

    function renderVariants(matrix) {
        var wrapper = findVariantsWrapper();
        if (!wrapper) return;

        var body = wrapper.querySelector('.variants-body');
        var tpl = wrapper.querySelector('.variant-row-template').innerHTML;

        var defaultPrice = wrapper.dataset.defaultPrice || '0';
        var defaultStock = wrapper.dataset.defaultStock || '0';
        var warningBox = wrapper.querySelector('.variants-warning');
        var emptyBox = wrapper.querySelector('.variants-empty');
        warningBox.classList.add('d-none');
        warningBox.textContent = '';

        // Gộp giá trị đang có trên DOM vào bộ nhớ (không ghi đè dữ liệu cũ đã lưu)
        body.querySelectorAll('.variant-row').forEach(function (row) {
            var key = row.dataset.key;
            enteredValues[key] = collectRowData(row);
        });

        // Chỉ các attribute is_variation và đã chọn value mới sinh tổ hợp
        var variationAttributes = (matrix || []).filter(function (a) {
            return a.is_variation && a.value_ids && a.value_ids.length > 0;
        });

        if (!variationAttributes.length) {
            body.innerHTML = '';
            emptyBox.classList.remove('d-none');
            return;
        }

        var comboCount = 1;
        variationAttributes.forEach(function (a) { comboCount *= a.value_ids.length; });

        if (comboCount > MAX_COMBOS) {
            body.innerHTML = '';
            emptyBox.classList.remove('d-none');
            warningBox.textContent = '{{ __('Tổ hợp biến thể quá lớn (:count). Tối đa :max tổ hợp được phép.', ['max' => config('products.max_combos', 100)]) }}'.replace(':count', comboCount);
            warningBox.classList.remove('d-none');
            return;
        }

        // Cartesian product
        var combos = [[]];
        variationAttributes.forEach(function (attribute) {
            var next = [];
            combos.forEach(function (partial) {
                attribute.value_ids.forEach(function (valueId) {
                    next.push(partial.concat([valueId]));
                });
            });
            combos = next;
        });

        // Map id → tên giá trị để hiển thị tên biến thể (value_names là object id→name)
        var attrValueNames = {};
        (matrix || []).forEach(function (attribute) {
            Object.keys(attribute.value_names || {}).forEach(function (id) {
                attrValueNames[id] = attribute.value_names[id];
            });
        });

        emptyBox.classList.add('d-none');
        body.innerHTML = '';

        var uid = 'v' + Date.now();

        combos.forEach(function (combo, order) {
            var sorted = combo.slice().sort(function (a, b) { return a - b; });
            var key = sorted.join('-');
            var name = sorted.map(function (id) { return attrValueNames[id] || ('#' + id); }).join(' - ');
            var prev = enteredValues[key] || {};

            var html = tpl
                .split('__KEY__').join(key)
                .split('__NAME__').join(escapeHtml(name))
                .split('__DEFAULT_PRICE__').join(defaultPrice)
                .split('__DEFAULT_STOCK__').join(prev.stock !== undefined ? prev.stock : defaultStock)
                .split('__ACTIVE_ID__').join('variant_active_' + key + '_' + uid + '_' + order);

            var holder = document.createElement('tbody');
            holder.innerHTML = html.trim();
            var row = holder.firstElementChild;

            if (prev.sku !== undefined) {
                applyRowData(row, prev);
            }

            body.appendChild(row);
        });
    }

    function collectRowData(row) {
        ensureHiddenFields(row, null);

        return {
            sku: row.querySelector('.variant-sku').value,
            price: row.querySelector('.variant-price').value,
            compare_price: row.querySelector('.variant-compare_price').value,
            cost_price: row.querySelector('.variant-cost_price').value,
            barcode: row.querySelector('.variant-barcode').value,
            image: row.querySelector('.variant-image-uuid').value,
            stock: row.querySelector('.variant-stock').value,
            is_active: row.querySelector('input[type="checkbox"]').checked
        };
    }

    function applyRowData(row, data) {
        row.querySelector('.variant-sku').value = data.sku || '';
        row.querySelector('.variant-price').value = data.price || '';
        row.querySelector('.variant-stock').value = data.stock !== undefined ? data.stock : '';
        row.querySelector('input[type="checkbox"]').checked = data.is_active !== false;

        // Các trường phụ (modal chi tiết) + ảnh thumbnail
        ensureHiddenFields(row, data);
        applyThumb(row, data.image);
    }

    function ensureHiddenFields(row, data) {
        var fields = ['compare_price', 'cost_price', 'barcode'];
        fields.forEach(function (field) {
            var input = row.querySelector('.variant-' + field);
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.className = 'variant-' + field;
                input.name = 'variants[' + row.dataset.key + '][' + field + ']';
                row.appendChild(input);
            }
            if (data && data[field] !== undefined) {
                input.value = data[field];
            }
        });

        var imgInput = row.querySelector('.variant-image-uuid');
        if (!imgInput) {
            imgInput = document.createElement('input');
            imgInput.type = 'hidden';
            imgInput.className = 'variant-image-uuid';
            imgInput.name = 'variants[' + row.dataset.key + '][image_uuid]';
            row.appendChild(imgInput);
        }
        if (data && data.image !== undefined) {
            imgInput.value = data.image;
        }
    }

    function applyThumb(row, image) {
        var thumb = row.querySelector('.variant-thumb');
        if (!thumb) return;

        if (image) {
            thumb.classList.remove('d-none');
            thumb.src = image;
        } else {
            thumb.classList.add('d-none');
            thumb.src = '';
        }
    }

    // ---- Toolbar hàng loạt ---------------------------------------------------

    document.body.addEventListener('click', function (e) {
        var wrapper = findVariantsWrapper();
        if (!wrapper) return;

        var genSkus = e.target.closest('.btn-generate-skus');
        if (genSkus) {
            var prefix = '{{ config('products.sku_prefix', 'PRD-') }}';
            wrapper.querySelectorAll('.variant-row').forEach(function (row, i) {
                row.querySelector('.variant-sku').value = prefix + String(i + 1).padStart(3, '0');
            });
            return;
        }

        var applyPrice = e.target.closest('.btn-apply-price');
        if (applyPrice) {
            var price = prompt('{{ __('Nhập giá áp dụng cho tất cả biến thể:') }}');
            if (price === null) return;
            wrapper.querySelectorAll('.variant-price').forEach(function (input) {
                input.value = price;
            });
            return;
        }

        var applyStock = e.target.closest('.btn-apply-stock');
        if (applyStock) {
            var stock = prompt('{{ __('Nhập tồn kho áp dụng cho tất cả biến thể:') }}');
            if (stock === null) return;
            wrapper.querySelectorAll('.variant-stock').forEach(function (input) {
                input.value = stock;
            });
            return;
        }

        // ---- Modal chi tiết ---------------------------------------------------

        var detailBtn = e.target.closest('.btn-variant-detail');
        if (detailBtn) {
            openVariantDetail(detailBtn);
            return;
        }

        var saveDetail = e.target.closest('.btn-save-variant-detail');
        if (saveDetail) {
            saveVariantDetail();
            return;
        }
    });

    var detailRow = null;

    function openVariantDetail(btn) {
        var wrapper = findVariantsWrapper();
        if (!wrapper) return;

        detailRow = btn.closest('.variant-row');
        if (!detailRow) return;

        ensureHiddenFields(detailRow, null);

        var modal = document.getElementById(wrapper.id + '_detail');
        if (!modal) return;

        var name = detailRow.querySelector('.variant-name')
            ? detailRow.querySelector('.variant-name').textContent.trim()
            : detailRow.dataset.key;
        modal.querySelector('.modal-title').textContent = '{{ __('Chi tiết biến thể') }}: ' + name;

        modal.querySelector('[data-field="compare_price"]').value =
            detailRow.querySelector('.variant-compare_price').value || '';
        modal.querySelector('[data-field="cost_price"]').value =
            detailRow.querySelector('.variant-cost_price').value || '';
        modal.querySelector('[data-field="barcode"]').value =
            detailRow.querySelector('.variant-barcode').value || '';

        // Ảnh biến thể: preview + uuid
        var imgUpload = modal.querySelector('.detail-image-upload');
        if (imgUpload) {
            var uuidInput = imgUpload.querySelector('.input-media-uuid');
            var previewImg = imgUpload.querySelector('.preview-image');
            var placeholder = imgUpload.querySelector('.placeholder');
            var clearBtn = imgUpload.querySelector('.btn-clear-image');
            var removeInput = imgUpload.querySelector('.input-remove-flag');

            var uuid = detailRow.querySelector('.variant-image-uuid').value;

            if (uuidInput) uuidInput.value = uuid || '';
            if (removeInput) removeInput.value = '0';

            if (uuid && previewImg) {
                // Chỉ URL tuyệt đối render được trực tiếp; uuid cần server-side
                if (/^https?:\/\//i.test(uuid)) {
                    previewImg.src = uuid;
                }
                previewImg.classList.remove('d-none');
                if (placeholder) placeholder.classList.add('d-none');
                if (clearBtn) clearBtn.classList.remove('d-none');
            } else {
                previewImg.src = '';
                previewImg.classList.add('d-none');
                if (placeholder) placeholder.classList.remove('d-none');
                if (clearBtn) clearBtn.classList.add('d-none');
            }
        }

        openModal(modal.id);
    }

    function saveVariantDetail() {
        if (!detailRow) return;

        var wrapper = findVariantsWrapper();
        var modal = document.getElementById(wrapper.id + '_detail');
        if (!modal) return;

        modal.querySelectorAll('.detail-field').forEach(function (input) {
            var field = input.dataset.field;
            var hidden = detailRow.querySelector('.variant-' + field);
            if (hidden) hidden.value = input.value;
        });

        // Ảnh biến thể: image-upload hidden input tên {name}_uuid
        var imgUpload = modal.querySelector('.detail-image-upload');
        if (imgUpload) {
            var uuidInput = imgUpload.querySelector('.input-media-uuid');
            var hidden = detailRow.querySelector('.variant-image-uuid');
            if (uuidInput && hidden) {
                hidden.value = uuidInput.value;
                applyThumb(detailRow, uuidInput.value);
            }
        }

        var key = detailRow.dataset.key;
        enteredValues[key] = collectRowData(detailRow);

        closeModal(modal.id);
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
});
</script>
@endpush
@endonce
