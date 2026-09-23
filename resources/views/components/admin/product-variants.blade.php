@props([
    'product'         => null,
    'defaultPrice'    => 0,
    'defaultStock'    => 0,
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
                'price'         => $variant->price !== null ? (float) $variant->price : null,
                'compare_price' => $variant->compare_price !== null ? (float) $variant->compare_price : null,
                'stock'         => (int) $variant->stock_quantity,
                'is_active'     => (bool) $variant->is_active,
            ];
        }
    }
@endphp

<div class="product-variants-wrapper" id="{{ $wrapperId }}"
     data-default-price="{{ (float) $defaultPrice }}"
     data-default-stock="{{ (int) $defaultStock }}">

    <p class="text-body-secondary small mb-3">
        {{ __('Bảng biến thể tự sinh từ các thuộc tính được đánh dấu "Dùng cho biến thể". Thêm/bỏ giá trị để cập nhật tổ hợp; dữ liệu đã nhập của tổ hợp còn hợp lệ sẽ được giữ nguyên.') }}
    </p>

    <div class="table-responsive">
        <table class="table table-sm align-middle table-borderless mb-0 variants-table">
            <thead>
                <tr class="border-bottom text-body-secondary small">
                    <th>{{ __('Biến thể') }}</th>
                    <th style="width: 150px;">{{ __('Mã SKU') }}</th>
                    <th style="width: 120px;">{{ __('Giá (đ)') }}</th>
                    <th style="width: 130px;">{{ __('Giá so sánh (đ)') }}</th>
                    <th style="width: 100px;">{{ __('Tồn kho') }}</th>
                    <th style="width: 60px;" class="text-center">{{ __('Hoạt động') }}</th>
                </tr>
            </thead>
            <tbody class="variants-body">
                @foreach ($rows as $row)
                    <tr class="variant-row" data-key="{{ $row['key'] }}">
                        <td>
                            <span class="fw-medium">{{ $row['name'] }}</span>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm variant-sku" name="variants[{{ $row['key'] }}][sku]" value="{{ $row['sku'] }}" placeholder="{{ __('SKU') }}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm variant-price" name="variants[{{ $row['key'] }}][price]" value="{{ $row['price'] }}" min="0" step="1000" placeholder="{{ $defaultPrice }}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm variant-compare-price" name="variants[{{ $row['key'] }}][compare_price]" value="{{ $row['compare_price'] }}" min="0" step="1000">
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
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <p class="variants-empty text-body-secondary small mb-0 mt-2 @if (!empty($rows)) d-none @endif">
        {{ __('Chưa có biến thể. Đánh dấu "Dùng cho biến thể" ở các thuộc tính và chọn giá trị để hệ thống tự sinh tổ hợp.') }}
    </p>

    <div class="variants-warning text-warning small mt-2 d-none" role="alert"></div>

    {{-- Template dòng biến thể mới (clone bằng JS) --}}
    <template class="variant-row-template">
        <tr class="variant-row" data-key="__KEY__">
            <td><span class="fw-medium variant-name">__NAME__</span></td>
            <td>
                <input type="text" class="form-control form-control-sm variant-sku" name="variants[__KEY__][sku]" placeholder="{{ __('SKU') }}">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm variant-price" name="variants[__KEY__][price]" min="0" step="1000" placeholder="__DEFAULT_PRICE__">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm variant-compare-price" name="variants[__KEY__][compare_price]" min="0" step="1000">
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
        </tr>
    </template>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var MAX_COMBOS = 100;

    document.querySelectorAll('.product-attributes-wrapper').forEach(function (attributesWrapper) {
        attributesWrapper.addEventListener('product-attributes:change', function (e) {
            renderVariants(e.detail.matrix);
        });
    });

    function findVariantsWrapper() {
        return document.querySelector('.product-variants-wrapper');
    }

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

        // Lưu giá trị đã nhập theo key để preserve khi re-render
        var entered = {};
        body.querySelectorAll('.variant-row').forEach(function (row) {
            var key = row.dataset.key;
            entered[key] = {
                sku: row.querySelector('.variant-sku').value,
                price: row.querySelector('.variant-price').value,
                compare_price: row.querySelector('.variant-compare-price').value,
                stock: row.querySelector('.variant-stock').value,
                is_active: row.querySelector('input[type="checkbox"]').checked
            };
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
            warningBox.textContent = '{{ __('Tổ hợp biến thể quá lớn (:count). Tối đa :max tổ hợp được phép.', ['max' => 100]) }}'.replace(':count', comboCount);
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

        var attrValueNames = {};
        (matrix || []).forEach(function (attribute) {
            (attribute.value_names || []).forEach(function (value) {
                attrValueNames[value.id] = value;
            });
        });

        emptyBox.classList.add('d-none');
        body.innerHTML = '';

        var uid = 'v' + Date.now();

        combos.forEach(function (combo, order) {
            var sorted = combo.slice().sort(function (a, b) { return a - b; });
            var key = sorted.join('-');
            var name = sorted.map(function (id) { return attrValueNames[id] || ('#' + id); }).join(' - ');
            var prev = entered[key] || {};

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
                row.querySelector('.variant-sku').value = prev.sku;
                row.querySelector('.variant-price').value = prev.price;
                row.querySelector('.variant-compare-price').value = prev.compare_price;
                row.querySelector('.variant-stock').value = prev.stock;
                row.querySelector('input[type="checkbox"]').checked = prev.is_active;
            }

            body.appendChild(row);
        });
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
