@props([
    'product'           => null,
    'catalogAttributes' => null,
    'defaultLocale'     => null,
    'storeRoute'        => null,
])

@php
    $wrapperId = 'product_attributes_' . uniqid();

    // Lựa chọn hiện có của product (pivot + values đã chọn)
    $selected = [];
    $newAttributeIndex = 0;

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
                'new_values'   => [],
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
     data-default-locale="{{ $defaultLocale ?? app()->getLocale() }}"
     data-catalog="{{ json_encode($catalog, JSON_UNESCAPED_UNICODE) }}">

    <p class="text-body-secondary small mb-3">
        {{ __('Chọn thuộc tính → chọn các giá trị (có thể gõ giá trị mới) → bật "Dùng làm trục biến thể" ở các thuộc tính cần phân loại. Bảng biến thể bên dưới sẽ tự sinh từ tổ hợp giá trị đã chọn.') }}
    </p>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <div class="dropdown">
            <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle d-inline-flex align-items-center gap-1" data-bs-toggle="dropdown" aria-expanded="false">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>{{ __('Thêm thuộc tính') }}</span>
            </button>
            <ul class="dropdown-menu shadow" style="min-width: 240px; max-height: 320px; overflow-y: auto; --bs-dropdown-zindex: 1060;"></ul>
        </div>

        <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1 btn-toggle-custom-attribute" title="{{ __('Tạo thuộc tính mới cùng lúc với sản phẩm') }}">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <span>{{ __('Thuộc tính tùy chỉnh') }}</span>
        </button>
    </div>

    {{-- Form tạo thuộc tính tùy chỉnh (submit cùng form chính) --}}
    <div class="custom-attribute-form p-3 bg-body-tertiary rounded mb-3 d-none">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-medium mb-1">{{ __('Tên thuộc tính') }}</label>
                <input type="text" class="form-control form-control-sm custom-attribute-name" placeholder="{{ __('Ví dụ: Chất liệu') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-medium mb-1">{{ __('Loại hiển thị') }}</label>
                <select class="form-select form-select-sm custom-attribute-type">
                    <option value="select">{{ __('Dropdown') }}</option>
                    <option value="color">{{ __('Màu sắc') }}</option>
                    <option value="button">{{ __('Nút bấm') }}</option>
                    <option value="radio">{{ __('Radio') }}</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small fw-medium mb-1">{{ __('Giá trị (cách nhau dấu phẩy)') }}</label>
                <input type="text" class="form-control form-control-sm custom-attribute-values" placeholder="{{ __('Ví dụ: Cotton, Poly, Len') }}">
            </div>
        </div>
        <div class="d-flex gap-2 mt-2">
            <button type="button" class="btn btn-primary btn-sm btn-save-custom-attribute">{{ __('Thêm thuộc tính') }}</button>
            <button type="button" class="btn btn-outline-secondary btn-sm btn-cancel-custom-attribute">{{ __('Hủy') }}</button>
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

                <select multiple
                        name="attributes[{{ $attributeId }}][value_ids][]"
                        class="attribute-values-select"
                        data-attribute-id="{{ $attributeId }}">
                    @foreach ($attribute['values'] as $value)
                        <option value="{{ $value['id'] }}" @selected(in_array($value['id'], $attribute['value_ids'], true))>{{ $value['value'] }}</option>
                    @endforeach
                </select>

                <div class="form-check form-switch mb-0 mt-2">
                    @php $variationId = 'attr_' . $attributeId . '_variation_' . uniqid(); @endphp
                    <input class="form-check-input variation-switch" type="checkbox" role="switch" id="{{ $variationId }}" name="attributes[{{ $attributeId }}][is_variation]" value="1" @checked($attribute['is_variation'])>
                    <label class="form-check-label small fw-medium" for="{{ $variationId }}">{{ __('Dùng làm trục biến thể (sinh tổ hợp)') }}</label>
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

            <select multiple
                    name="attributes[__ATTR_ID__][value_ids][]"
                    class="attribute-values-select"
                    data-attribute-id="__ATTR_ID__">__ATTR_VALUES__</select>

            <div class="form-check form-switch mb-0 mt-2">
                <input class="form-check-input variation-switch" type="checkbox" role="switch" id="__VARIATION_ID__" name="attributes[__ATTR_ID__][is_variation]" value="1">
                <label class="form-check-label small fw-medium" for="__VARIATION_ID__">{{ __('Dùng làm trục biến thể (sinh tổ hợp)') }}</label>
            </div>
        </div>
    </template>

    {{-- Template option cho tom-select --}}
    <template class="attribute-value-option-template">
        <option value="__VALUE_ID__">__VALUE_TEXT__</option>
    </template>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---- TomSelect bootstrapping ------------------------------------------

    function initTomSelect(select) {
        if (select.dataset.tomSelectInitialized) return;
        select.dataset.tomSelectInitialized = '1';

        if (!window.TomSelect) return;

        new window.TomSelect(select, {
            plugins: ['remove_button', 'clear_button'],
            create: true,
            createOnBlur: true,
            maxItems: null,
            hideSelected: true,
            allowEmptyOption: true,
            render: {
                option: function (data, escape) {
                    var color = data.color_code
                        ? '<span class="rounded-circle border d-inline-block" style="width:12px;height:12px;background-color:' + escape(data.color_code) + ';"></span>'
                        : '';
                    return '<div class="d-flex align-items-center gap-2">' + color + '<span>' + escape(data.text) + '</span></div>';
                },
                item: function (data, escape) {
                    var color = data.color_code
                        ? '<span class="rounded-circle border d-inline-block" style="width:12px;height:12px;background-color:' + escape(data.color_code) + ';"></span>'
                        : '';
                    return '<div class="d-flex align-items-center gap-2">' + color + '<span>' + escape(data.text) + '</span></div>';
                }
            }
        });
    }

    function initAllTomSelects(scope) {
        (scope || document).querySelectorAll('.attribute-values-select').forEach(initTomSelect);
    }

    initAllTomSelects();

    // ---- Event delegation ----------------------------------------------------

    document.body.addEventListener('click', function (e) {
        // Xóa thuộc tính
        var removeBtn = e.target.closest('.btn-remove-attribute');
        if (removeBtn) {
            var row = removeBtn.closest('.attribute-row');
            var wrapper = getWrapper(removeBtn);
            if (row.dataset.isNewAttribute === '1') {
                row.remove();
            } else {
                row.remove();
            }
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

        // Thêm thuộc tính tùy chỉnh vào form (submit cùng form chính)
        var saveCustom = e.target.closest('.btn-save-custom-attribute');
        if (saveCustom) {
            addCustomAttributeRow(saveCustom);
            return;
        }

        // Chọn thuộc tính từ dropdown catalog
        var catalogItem = e.target.closest('.dropdown-item.catalog-attribute-item');
        if (catalogItem) {
            var attrId = parseInt(catalogItem.dataset.attributeId, 10);
            var dataWrapper = catalogItem.closest('.product-attributes-wrapper');
            // Đóng dropdown TRƯỚC khi addAttributeRow (hàm đó render lại menu → node cũ bị xóa)
            var dropdownInstance = bootstrap.Dropdown.getInstance(catalogItem.closest('.dropdown-toggle'));
            if (dropdownInstance) {
                dropdownInstance.hide();
            }
            var catalog = JSON.parse(dataWrapper.dataset.catalog || '[]');
            var attribute = catalog.find(function (a) { return a.id === attrId; });
            if (attribute) {
                addAttributeRow(dataWrapper, attribute);
            }
        }
    });

    // TomSelect change + is_variation switch → thông báo cho block biến thể
    document.body.addEventListener('change', function (e) {
        if (
            e.target.classList.contains('variation-switch') ||
            e.target.classList.contains('attribute-values-select')
        ) {
            dispatchAttributesChange(e.target);
        }
    });

    function getWrapper(el) {
        return el.closest('.product-attributes-wrapper');
    }

    function getMatrix(wrapper) {
        var matrix = [];
        wrapper.querySelectorAll('.attribute-row').forEach(function (row) {
            var select = row.querySelector('.attribute-values-select');
            var valueIds = [];
            var valueNames = {};

            if (select && select.tomselect) {
                select.tomselect.items.forEach(function (value) {
                    var option = select.tomselect.getOption(value);
                    var name = option ? option.textContent.trim() : value;
                    // Tách id thực khỏi value mới tạo (tom-select dùng value.raw cho create)
                    var data = select.tomselect.options[value] || {};
                    if (data.created && data.value !== undefined) {
                        // giá trị mới: không có id, đánh dấu bằng chuỗi "new::<text>"
                        valueIds.push('new::' + data.text);
                        valueNames['new::' + data.text] = data.text;
                    } else {
                        valueIds.push(value);
                        valueNames[value] = name;
                    }
                });
            }

            matrix.push({
                attribute_id: row.dataset.attributeId,
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

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /**
     * Render một <option> (dùng cho cả server-render lẫn JS-build).
     */
    function buildOptionsHtml(wrapper, values) {
        var tpl = wrapper.querySelector('.attribute-value-option-template').innerHTML;
        return values.map(function (value) {
            return tpl
                .split('__VALUE_ID__').join(value.id)
                .split('__VALUE_TEXT__').join(escapeHtml(value.value));
        }).join('');
    }

    function addAttributeRow(wrapper, attribute) {
        // Đã tồn tại → bỏ qua
        var existing = wrapper.querySelector('.attribute-row[data-attribute-id="' + attribute.id + '"]');
        if (existing) return;

        var tpl = wrapper.querySelector('.attribute-row-template').innerHTML;
        var uid = 'u' + Date.now() + '_' + Math.random().toString(36).slice(2, 7);

        var html = tpl
            .split('__ATTR_ID__').join(attribute.id)
            .split('__ATTR_NAME__').join(escapeHtml(attribute.name))
            .split('__ATTR_TYPE__').join(escapeHtml(attribute.type))
            .split('__ATTR_VALUES__').join(buildOptionsHtml(wrapper, attribute.values))
            .split('__VARIATION_ID__').join('attr_' + attribute.id + '_variation_' + uid);

        var holder = document.createElement('div');
        holder.innerHTML = html.trim();

        var row = holder.firstElementChild;
        wrapper.querySelector('.attribute-list').appendChild(row);

        // Đánh dấu option màu cho tom-select
        var select = row.querySelector('.attribute-values-select');
        if (select) {
            attribute.values.forEach(function (value) {
                var option = select.querySelector('option[value="' + value.id + '"]');
                if (option && value.color_code) {
                    option.dataset.colorCode = value.color_code;
                }
            });
            attachColorData(wrapper, select);
            initTomSelect(select);
        }

        refreshEmptyHint(wrapper);
        renderCatalogDropdown(wrapper);
        dispatchAttributesChange(wrapper);
    }

    /**
     * Cài color_code vào option dataset để tom-select render swatch.
     */
    function attachColorData(wrapper, select) {
        var catalog = JSON.parse(wrapper.dataset.catalog || '[]');
        var attrId = select.dataset.attributeId;
        var found = catalog.find(function (a) { return String(a.id) === String(attrId); });
        if (!found) return;
        found.values.forEach(function (v) {
            var option = select.querySelector('option[value="' + v.id + '"]');
            if (option && v.color_code) option.dataset.colorCode = v.color_code;
        });
    }

    /**
     * Tạo dòng thuộc tính tùy chỉnh KHÔNG AJAX: dữ liệu submit cùng form
     * chính qua new_attributes[], giá trị tạm thời giữ ở ẩn (server tạo
     * attribute thật sau khi product save).
     */
    function addCustomAttributeRow(btn) {
        var wrapper = getWrapper(btn);
        var locale = wrapper.dataset.defaultLocale || 'vi';
        var nameInput = wrapper.querySelector('.custom-attribute-name');
        var typeInput = wrapper.querySelector('.custom-attribute-type');
        var valuesInput = wrapper.querySelector('.custom-attribute-values');
        var errorBox = wrapper.querySelector('.custom-attribute-error');

        errorBox.classList.add('d-none');

        var name = nameInput.value.trim();
        var rawValues = valuesInput.value.split(',').map(function (v) { return v.trim(); }).filter(Boolean);

        if (!name || !rawValues.length) {
            errorBox.textContent = '{{ __('Vui lòng nhập tên thuộc tính và ít nhất một giá trị.') }}';
            errorBox.classList.remove('d-none');
            return;
        }

        // Đếm số new attribute đã có để sinh key duy nhất
        var newCount = wrapper.querySelectorAll('.attribute-row[data-is-new-attribute="1"]').length;
        var tempId = 'new_' + Date.now() + '_' + newCount;

        var values = rawValues.map(function (v, i) {
            return { id: 'new::' + v, value: v, display_order: i };
        });

        // ---- Hidden payload: new_attributes[] ----
        var payload = document.createElement('div');
        payload.className = 'new-attribute-payload';
        payload.innerHTML =
            '<input type="hidden" name="new_attributes[' + tempId + '][name]" value="' + escapeHtml(name) + '">' +
            '<input type="hidden" name="new_attributes[' + tempId + '][type]" value="' + escapeHtml(typeInput.value) + '">' +
            rawValues.map(function (v, i) {
                return '<input type="hidden" name="new_attributes[' + tempId + '][values][' + i + ']" value="' + escapeHtml(v) + '">';
            }).join('');

        // ---- Row hiển thị (tạm thời dùng tempId làm data-attribute-id) ----
        var attribute = {
            id: tempId,
            name: name,
            type: typeInput.value,
            values: values
        };

        // Đưa custom attribute vào catalog tạm để addAttributeRow dùng
        var catalog = JSON.parse(wrapper.dataset.catalog || '[]');
        catalog.push(attribute);
        wrapper.dataset.catalog = JSON.stringify(catalog);

        addAttributeRow(wrapper, attribute);

        // Đánh dấu là new attribute + gắn payload
        var row = wrapper.querySelector('.attribute-row[data-attribute-id="' + tempId + '"]');
        if (row) {
            row.dataset.isNewAttribute = '1';
            row.appendChild(payload);

            // Bỏ hidden input attribute_id (chưa có id thực)
            var idInput = row.querySelector('input[name$="[attribute_id]"]');
            if (idInput) idInput.remove();

            // Giữ select name nhất quán — server parse sẽ bỏ qua tempId
            var select = row.querySelector('.attribute-values-select');
            if (select) {
                select.name = 'new_attribute_values[' + tempId + '][value_ids][]';
            }
        }

        nameInput.value = '';
        valuesInput.value = '';
        wrapper.querySelector('.custom-attribute-form').classList.add('d-none');
    }

    function renderCatalogDropdown(wrapper) {
        var catalog = JSON.parse(wrapper.dataset.catalog || '[]');
        var usedIds = Array.prototype.map.call(
            wrapper.querySelectorAll('.attribute-row'),
            function (row) { return row.dataset.attributeId; }
        );

        var menu = wrapper.querySelector('.dropdown-menu');
        menu.innerHTML = '';

        // Ô tìm kiếm (luôn ở đầu, không bị xóa khi lọc)
        var searchWrapper = document.createElement('li');
        searchWrapper.className = 'px-2 pb-2 border-bottom';
        var searchInput = document.createElement('input');
        searchInput.type = 'search';
        searchInput.className = 'form-control form-control-sm catalog-search';
        searchInput.placeholder = 'Tìm thuộc tính...';
        searchInput.autocomplete = 'off';
        // Tránh Bootstrap đóng dropdown khi click vào ô tìm kiếm
        searchInput.addEventListener('click', function (e) { e.stopPropagation(); });
        searchInput.addEventListener('input', function () {
            filterCatalogItems(menu, this.value);
        });
        searchWrapper.appendChild(searchInput);
        menu.appendChild(searchWrapper);

        var available = catalog.filter(function (a) { return usedIds.indexOf(String(a.id)) === -1; });

        if (!available.length) {
            var emptyLi = document.createElement('li');
            var emptySpan = document.createElement('span');
            emptySpan.className = 'dropdown-item-text text-body-secondary small';
            emptySpan.textContent = '{{ __('Đã chọn hết thuộc tính sẵn có') }}';
            emptyLi.appendChild(emptySpan);
            menu.appendChild(emptyLi);
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

    // Khởi tạo dropdown cho tất cả wrapper
    document.querySelectorAll('.product-attributes-wrapper').forEach(renderCatalogDropdown);
});
</script>
@endpush
@endonce
