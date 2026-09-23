@props([
    'product'           => null,
    'catalogAttributes' => null,
    'storeRoute'        => null,
])

@php
    $wrapperId = 'product_attributes_' . uniqid();

    // Lựa chọn hiện có của product (pivot + values đã chọn)
    $selected = [];

    if ($product) {
        foreach ($product->attributes as $attribute) {
            $selected[$attribute->id] = [
                'id'           => $attribute->id,
                'name'         => $attribute->name,
                'type'         => $attribute->type,
                'is_variation' => (bool) $attribute->pivot->is_variation,
                'values'       => $attribute->values->map(fn ($v) => [
                    'id'         => $v->id,
                    'value'      => $v->value,
                    'color_code' => $v->color_code,
                ])->values()->toArray(),
                'value_ids'    => [],
            ];
        }

        foreach ($product->attributeValues as $value) {
            if (isset($selected[$value->attribute_id])) {
                $selected[$value->attribute_id]['value_ids'][] = $value->id;
            }
        }
    }

    $catalog = collect($catalogAttributes ?? [])->map(fn ($a) => [
        'id'     => $a->id,
        'name'   => $a->name,
        'type'   => $a->type,
        'values' => $a->values->map(fn ($v) => [
            'id'         => $v->id,
            'value'      => $v->value,
            'color_code' => $v->color_code,
        ])->values()->toArray(),
    ])->values();
@endphp

<div class="product-attributes-wrapper" id="{{ $wrapperId }}"
     data-store-route="{{ $storeRoute }}"
     data-catalog="{{ json_encode($catalog, JSON_UNESCAPED_UNICODE) }}">

    <div class="d-flex flex-wrap gap-2 mb-3">
        <div class="dropdown">
            <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle d-inline-flex align-items-center gap-1" data-bs-toggle="dropdown" aria-expanded="false">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>{{ __('Thêm thuộc tính') }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow" style="max-height: 280px; overflow-y: auto;"></ul>
        </div>

        <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 btn-toggle-custom-attribute">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <span>{{ __('Thuộc tính tùy chỉnh') }}</span>
        </button>
    </div>

    {{-- Form tạo thuộc tính tùy chỉnh (inline) --}}
    <div class="custom-attribute-form p-3 bg-body-tertiary rounded mb-3 d-none">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-medium mb-1">{{ __('Tên thuộc tính') }}</label>
                <input type="text" class="form-control form-control-sm custom-attribute-name" placeholder="{{ __('Ví dụ: Chất liệu') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-medium mb-1">{{ __('Giá trị (cách nhau dấu phẩy)') }}</label>
                <input type="text" class="form-control form-control-sm custom-attribute-values" placeholder="{{ __('Ví dụ: Cotton, Poly, Len') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="button" class="btn btn-primary btn-sm flex-fill btn-save-custom-attribute">{{ __('Lưu') }}</button>
                <button type="button" class="btn btn-outline-secondary btn-sm btn-cancel-custom-attribute">{{ __('Hủy') }}</button>
            </div>
        </div>
        <div class="custom-attribute-error text-danger small mt-2 d-none"></div>
    </div>

    {{-- Danh sách thuộc tính đã chọn --}}
    <div class="attribute-list d-flex flex-column gap-3">
        @foreach ($selected as $attributeId => $attribute)
            <div class="attribute-row border rounded p-3" data-attribute-id="{{ $attributeId }}">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <strong class="attribute-name">{{ $attribute['name'] }}</strong>
                        <span class="badge text-bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 attribute-type">{{ $attribute['type'] }}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-attribute" title="{{ __('Xóa thuộc tính') }}">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>

                <input type="hidden" name="attributes[{{ $attributeId }}][attribute_id]" value="{{ $attributeId }}">

                <div class="value-chips d-flex flex-wrap gap-2 mb-2">
                    @foreach ($attribute['values'] as $value)
                        @php
                            $chipId = 'attr_' . $attributeId . '_val_' . $value['id'] . '_' . uniqid();
                            $checked = in_array($value['id'], $attribute['value_ids'], true);
                        @endphp
                        <input type="checkbox" class="btn-check value-chip" id="{{ $chipId }}" name="attributes[{{ $attributeId }}][value_ids][]" value="{{ $value['id'] }}" autocomplete="off" @checked($checked)>
                        <label class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1" for="{{ $chipId }}">
                            @if ($attribute['type'] === 'color' && !empty($value['color_code']))
                                <span class="rounded-circle border" style="width: 14px; height: 14px; background-color: {{ $value['color_code'] }};"></span>
                            @endif
                            <span>{{ $value['value'] }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="form-check form-switch mb-0">
                    @php $variationId = 'attr_' . $attributeId . '_variation_' . uniqid(); @endphp
                    <input class="form-check-input variation-switch" type="checkbox" role="switch" id="{{ $variationId }}" name="attributes[{{ $attributeId }}][is_variation]" value="1" @checked($attribute['is_variation'])>
                    <label class="form-check-label small fw-medium" for="{{ $variationId }}">{{ __('Dùng cho biến thể') }}</label>
                </div>
            </div>
        @endforeach
    </div>

    <p class="attribute-empty-hint text-body-secondary small mb-0 @if (!empty($selected)) d-none @endif">{{ __('Chưa có thuộc tính nào. Nhấn "Thêm thuộc tính" để chọn từ danh mục chung hoặc "Thuộc tính tùy chỉnh" để tạo mới.') }}</p>

    {{-- Template dòng thuộc tính mới (clone bằng JS) --}}
    <template class="attribute-row-template">
        <div class="attribute-row border rounded p-3" data-attribute-id="__ATTR_ID__">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <strong class="attribute-name">__ATTR_NAME__</strong>
                    <span class="badge text-bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 attribute-type">__ATTR_TYPE__</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-attribute" title="{{ __('Xóa thuộc tính') }}">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>

            <input type="hidden" name="attributes[__ATTR_ID__][attribute_id]" value="__ATTR_ID__">

            <div class="value-chips d-flex flex-wrap gap-2 mb-2">__ATTR_VALUES__</div>

            <div class="form-check form-switch mb-0">
                <input class="form-check-input variation-switch" type="checkbox" role="switch" id="__VARIATION_ID__" name="attributes[__ATTR_ID__][is_variation]" value="1">
                <label class="form-check-label small fw-medium" for="__VARIATION_ID__">{{ __('Dùng cho biến thể') }}</label>
            </div>
        </div>
    </template>

    {{-- Template chip giá trị --}}
    <template class="value-chip-template">
        <input type="checkbox" class="btn-check value-chip" id="__CHIP_ID__" name="attributes[__ATTR_ID__][value_ids][]" value="__VALUE_ID__" autocomplete="off">
        <label class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1" for="__CHIP_ID__">__CHIP_INNER__</label>
    </template>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.body.addEventListener('click', function (e) {
        // Xóa thuộc tính
        var removeBtn = e.target.closest('.btn-remove-attribute');
        if (removeBtn) {
            removeBtn.closest('.attribute-row').remove();
            dispatchAttributesChange(removeBtn);
            refreshEmptyHint(removeBtn);
            return;
        }

        // Toggle form tạo thuộc tính tùy chỉnh
        var toggleCustom = e.target.closest('.btn-toggle-custom-attribute');
        if (toggleCustom) {
            var wrapper = toggleCustom.closest('.product-attributes-wrapper');
            wrapper.querySelector('.custom-attribute-form').classList.toggle('d-none');
            return;
        }

        var cancelCustom = e.target.closest('.btn-cancel-custom-attribute');
        if (cancelCustom) {
            var w = cancelCustom.closest('.product-attributes-wrapper');
            w.querySelector('.custom-attribute-form').classList.add('d-none');
            w.querySelector('.custom-attribute-error').classList.add('d-none');
            return;
        }

        // Lưu thuộc tính tùy chỉnh (AJAX)
        var saveCustom = e.target.closest('.btn-save-custom-attribute');
        if (saveCustom) {
            saveCustomAttribute(saveCustom);
            return;
        }

        // Chọn thuộc tính từ dropdown catalog
        var catalogItem = e.target.closest('.dropdown-item.catalog-attribute-item');
        if (catalogItem) {
            var attrId = parseInt(catalogItem.dataset.attributeId, 10);
            var dataWrapper = catalogItem.closest('.product-attributes-wrapper');
            var catalog = JSON.parse(dataWrapper.dataset.catalog || '[]');
            var attribute = catalog.find(function (a) { return a.id === attrId; });
            if (attribute) {
                addAttributeRow(dataWrapper, attribute);
            }
            catalogItem.closest('.dropdown-menu').classList.remove('show');
        }
    });

    // Chip value toggle + is_variation switch → thông báo cho block biến thể
    document.body.addEventListener('change', function (e) {
        if (e.target.classList.contains('value-chip') || e.target.classList.contains('variation-switch')) {
            dispatchAttributesChange(e.target);
        }
    });

    function getWrapper(el) {
        return el.closest('.product-attributes-wrapper');
    }

    function getMatrix(wrapper) {
        var matrix = [];
        wrapper.querySelectorAll('.attribute-row').forEach(function (row) {
            var valueIds = [];
            var valueNames = {};
            row.querySelectorAll('.value-chip').forEach(function (chip) {
                var label = document.querySelector('label[for="' + chip.id + '"]');
                var name = label ? label.textContent.trim() : ('#' + chip.value);
                valueNames[chip.value] = name;
                if (chip.checked) {
                    valueIds.push(parseInt(chip.value, 10));
                }
            });
            matrix.push({
                attribute_id: parseInt(row.dataset.attributeId, 10),
                is_variation: !!row.querySelector('.variation-switch').checked,
                value_ids: valueIds,
                value_names: valueNames
            });
        });
        return matrix;
    }

    function dispatchAttributesChange(el) {
        var wrapper = getWrapper(el);
        if (!wrapper) return;
        wrapper.dispatchEvent(new CustomEvent('product-attributes:change', {
            bubbles: true,
            detail: { matrix: getMatrix(wrapper) }
        }));
    }

    function refreshEmptyHint(el) {
        var wrapper = getWrapper(el);
        if (!wrapper) return;
        var hasRows = wrapper.querySelectorAll('.attribute-row').length > 0;
        wrapper.querySelector('.attribute-empty-hint').classList.toggle('d-none', hasRows);
    }

    function chipInner(value) {
        if (value.color_code) {
            return '<span class="rounded-circle border" style="width: 14px; height: 14px; background-color: ' + value.color_code + ';"></span><span>' + escapeHtml(value.value) + '</span>';
        }
        return '<span>' + escapeHtml(value.value) + '</span>';
    }

    function addAttributeRow(wrapper, attribute) {
        // Đã tồn tại → bỏ qua
        var existing = wrapper.querySelector('.attribute-row[data-attribute-id="' + attribute.id + '"]');
        if (existing) return;

        var tpl = wrapper.querySelector('.attribute-row-template').innerHTML;
        var chipTpl = wrapper.querySelector('.value-chip-template').innerHTML;
        var uid = 'u' + Date.now() + '_' + Math.random().toString(36).slice(2, 7);

        var chipsHtml = '';
        attribute.values.forEach(function (value) {
            var chipId = 'attr_' + attribute.id + '_val_' + value.id + '_' + uid;
            chipsHtml += chipTpl
                .split('__CHIP_ID__').join(chipId)
                .split('__ATTR_ID__').join(attribute.id)
                .split('__VALUE_ID__').join(value.id)
                .split('__CHIP_INNER__').join(chipInner(value));
        });

        var html = tpl
            .split('__ATTR_ID__').join(attribute.id)
            .split('__ATTR_NAME__').join(escapeHtml(attribute.name))
            .split('__ATTR_TYPE__').join(escapeHtml(attribute.type))
            .split('__ATTR_VALUES__').join(chipsHtml)
            .split('__VARIATION_ID__').join('attr_' + attribute.id + '_variation_' + uid);

        var holder = document.createElement('div');
        holder.innerHTML = html.trim();

        wrapper.querySelector('.attribute-list').appendChild(holder.firstElementChild);
        refreshEmptyHint(wrapper);
        renderCatalogDropdown(wrapper);
    }

    function renderCatalogDropdown(wrapper) {
        var catalog = JSON.parse(wrapper.dataset.catalog || '[]');
        var usedIds = Array.prototype.map.call(
            wrapper.querySelectorAll('.attribute-row'),
            function (row) { return parseInt(row.dataset.attributeId, 10); }
        );

        var menu = wrapper.querySelector('.dropdown-menu');
        menu.innerHTML = '';

        var available = catalog.filter(function (a) { return usedIds.indexOf(a.id) === -1; });

        if (!available.length) {
            menu.innerHTML = '<li><span class="dropdown-item-text text-body-secondary small">{{ __('Đã chọn hết thuộc tính sẵn có') }}</span></li>';
            return;
        }

        available.forEach(function (a) {
            var li = document.createElement('li');
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'dropdown-item catalog-attribute-item';
            btn.dataset.attributeId = a.id;
            btn.textContent = a.name;
            li.appendChild(btn);
            menu.appendChild(li);
        });
    }

    function saveCustomAttribute(btn) {
        var wrapper = getWrapper(btn);
        var nameInput = wrapper.querySelector('.custom-attribute-name');
        var valuesInput = wrapper.querySelector('.custom-attribute-values');
        var errorBox = wrapper.querySelector('.custom-attribute-error');

        var name = nameInput.value.trim();
        var rawValues = valuesInput.value.split(',').map(function (v) { return v.trim(); }).filter(Boolean);

        errorBox.classList.add('d-none');

        if (!name || !rawValues.length) {
            errorBox.textContent = '{{ __('Vui lòng nhập tên thuộc tính và ít nhất một giá trị.') }}';
            errorBox.classList.remove('d-none');
            return;
        }

        btn.disabled = true;
        btn.textContent = '{{ __('Đang lưu...') }}';

        fetch(wrapper.dataset.storeRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                translations: { vi: { name: name } },
                values: rawValues.map(function (v, i) { return { value: v, display_order: i }; })
            })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                addAttributeRow(wrapper, data.attribute);
                nameInput.value = '';
                valuesInput.value = '';
                wrapper.querySelector('.custom-attribute-form').classList.add('d-none');
            } else {
                throw new Error(data.message || '{{ __('Không thể tạo thuộc tính.') }}');
            }
        })
        .catch(function (err) {
            errorBox.textContent = err.message;
            errorBox.classList.remove('d-none');
        })
        .finally(function () {
            btn.disabled = false;
            btn.textContent = '{{ __('Lưu') }}';
        });
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Khởi tạo dropdown cho tất cả wrapper
    document.querySelectorAll('.product-attributes-wrapper').forEach(renderCatalogDropdown);
});
</script>
@endpush
@endonce
