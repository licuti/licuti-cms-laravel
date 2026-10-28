@extends('layouts.admin')

@section('title', $isEdit ? __('Chỉnh sửa Flash Sale') : __('Thêm mới Flash Sale'))

@section('content')
    <form 
        action="{{ $isEdit ? route('admin.flash-sales.update', $flashSale->uuid) : route('admin.flash-sales.store') }}" 
        method="POST" 
        id="flash-sale-form"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        {{-- 1. Header & Breadcrumbs --}}
        <x-admin.page-header 
            :title="$isEdit ? __('Chỉnh sửa: :name', ['name' => $flashSale->name ?: __('Flash Sale')]) : __('Tạo mới Flash Sale')" 
            :subtitle="__('Thiết lập khung giờ, giá sốc và giới hạn số lượng mở bán cho từng sản phẩm')" 
            :breadcrumbs="[
                ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
                ['label' => __('Flash Sale'), 'url' => route('admin.flash-sales.index')],
                ['label' => $isEdit ? __('Chỉnh sửa') : __('Thêm mới')]
            ]"
        />

        {{-- 2. Hai cột: Main (9) & Sidebar (3) --}}
        <div class="row g-4 align-items-start">

            {{-- CỘT CHÍNH: col-md-8 col-lg-9 --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">

                {{-- Tabs ngôn ngữ --}}
                <x-admin.lang-tabs :active-languages="$activeLanguages" :default-locale="$defaultLocale" />

                {{-- Khối thông tin theo từng ngôn ngữ --}}
                @foreach($activeLanguages as $lang)
                    <div class="lang-panel {{ $lang->code === $defaultLocale ? '' : 'd-none' }}" data-lang-panel="{{ $lang->code }}">
                        <x-admin.card :title="__('Thông tin chiến dịch (:locale)', ['locale' => strtoupper($lang->code)])">
                            <x-admin.form-group 
                                :label="__('Tên chiến dịch Flash Sale')" 
                                :name="'translations[' . $lang->code . '][name]'" 
                                :required="$lang->code === 'vi'"
                            >
                                <x-admin.input 
                                    name="translations[{{ $lang->code }}][name]" 
                                    value="{{ old('translations.'.$lang->code.'.name', $flashSale?->translate($lang->code)?->name) }}" 
                                    placeholder="{{ __('Ví dụ: Giờ Vàng Giá Sốc 20h - 22h, Siêu Sale Cuối Tuần...') }}" 
                                    :required="$lang->code === 'vi'" 
                                />
                            </x-admin.form-group>

                            <x-admin.form-group 
                                :label="__('Mô tả / Thể lệ tham gia')" 
                                :name="'translations[' . $lang->code . '][description]'"
                            >
                                <x-admin.textarea 
                                    name="translations[{{ $lang->code }}][description]" 
                                    rows="3" 
                                    placeholder="{{ __('Mô tả ngắn hiển thị tại banner hoặc trang Flash Sale...') }}"
                                >{{ old('translations.'.$lang->code.'.description', $flashSale?->translate($lang->code)?->description) }}</x-admin.textarea>
                            </x-admin.form-group>
                        </x-admin.card>
                    </div>
                @endforeach

                {{-- Bảng danh sách sản phẩm tham gia --}}
                <x-admin.card :title="__('Sản phẩm tham gia Flash Sale')">
                    <x-slot:action>
                        <x-admin.button 
                            type="button" 
                            variant="primary" 
                            size="sm" 
                            id="btn-open-product-modal"
                            data-bs-toggle="modal" 
                            data-bs-target="#modal-select-products"
                        >
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>{{ __('Chọn sản phẩm') }}</span>
                        </x-admin.button>
                    </x-slot:action>

                    @error('products')
                        <div class="alert alert-danger py-2 small mb-3">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="flash-sale-products-table">
                            <thead class="table-light small">
                                <tr>
                                    <th style="min-width: 220px;">{{ __('Sản phẩm / Biến thể') }}</th>
                                    <th style="width: 130px;">{{ __('Giá niêm yết') }}</th>
                                    <th style="width: 160px;">{{ __('Giá Flash Sale') }} <span class="text-danger">*</span></th>
                                    <th style="width: 140px;">{{ __('Số lượng mở bán') }} <span class="text-danger">*</span></th>
                                    <th style="width: 100px;" class="text-center">{{ __('Đã bán') }}</th>
                                    <th style="width: 130px;">{{ __('Giới hạn/khách') }}</th>
                                    <th style="width: 60px;" class="text-center">{{ __('Xóa') }}</th>
                                </tr>
                            </thead>
                            <tbody id="flash-sale-products-tbody">
                                @php
                                    $existingProducts = old('products');
                                    if ($existingProducts === null && $isEdit && $flashSale) {
                                        $existingProducts = [];
                                        foreach ($flashSale->products as $fsp) {
                                            $existingProducts[] = [
                                                'product_id'         => $fsp->product_id,
                                                'product_variant_id' => $fsp->product_variant_id,
                                                'product_name'       => $fsp->product->name,
                                                'variant_name'       => $fsp->variant?->name,
                                                'sku'                => $fsp->variant?->sku ?: $fsp->product->sku,
                                                'regular_price'      => $fsp->variant ? (float)$fsp->variant->price : (float)$fsp->product->price,
                                                'flash_price'        => (float)$fsp->flash_price,
                                                'quantity_limit'     => $fsp->quantity_limit,
                                                'sold_quantity'      => $fsp->sold_quantity,
                                                'limit_per_user'     => $fsp->limit_per_user,
                                                'sort_order'         => $fsp->sort_order,
                                            ];
                                        }
                                    }
                                    $existingProducts = $existingProducts ?? [];
                                @endphp

                                @forelse($existingProducts as $index => $row)
                                    @php
                                        $productId = $row['product_id'] ?? null;
                                        $variantId = $row['product_variant_id'] ?? null;
                                        $pName = $row['product_name'] ?? '';
                                        $vName = $row['variant_name'] ?? '';
                                        $sku = $row['sku'] ?? '';
                                        $regularPrice = (float)($row['regular_price'] ?? 0);
                                        $flashPrice = $row['flash_price'] ?? '';
                                        $qtyLimit = $row['quantity_limit'] ?? 10;
                                        $soldQty = $row['sold_quantity'] ?? 0;
                                        $limitUser = $row['limit_per_user'] ?? 0;
                                        $sortOrder = $row['sort_order'] ?? $index;
                                    @endphp
                                    <tr data-key="{{ $productId }}-{{ $variantId ?: 0 }}">
                                        <td>
                                            <input type="hidden" name="products[{{ $index }}][product_id]" value="{{ $productId }}">
                                            <input type="hidden" name="products[{{ $index }}][product_variant_id]" value="{{ $variantId }}">
                                            <input type="hidden" name="products[{{ $index }}][product_name]" value="{{ $pName }}">
                                            <input type="hidden" name="products[{{ $index }}][variant_name]" value="{{ $vName }}">
                                            <input type="hidden" name="products[{{ $index }}][sku]" value="{{ $sku }}">
                                            <input type="hidden" name="products[{{ $index }}][regular_price]" value="{{ $regularPrice }}">
                                            <input type="hidden" name="products[{{ $index }}][sold_quantity]" value="{{ $soldQty }}">
                                            <input type="hidden" name="products[{{ $index }}][sort_order]" value="{{ $sortOrder }}">

                                            <div class="fw-medium text-body">{{ $pName }}</div>
                                            @if($vName)
                                                <div class="small text-primary fw-medium">{{ $vName }}</div>
                                            @endif
                                            @if($sku)
                                                <div class="small text-body-secondary font-monospace">{{ $sku }}</div>
                                            @endif
                                            @error("products.{$index}.product_id")
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <span class="small text-body-secondary text-decoration-line-through">
                                                {{ number_format($regularPrice, 0, ',', '.') }}đ
                                            </span>
                                        </td>
                                        <td>
                                            <input 
                                                type="number" 
                                                name="products[{{ $index }}][flash_price]" 
                                                value="{{ $flashPrice }}" 
                                                class="form-control form-control-sm @error("products.{$index}.flash_price") is-invalid @enderror"
                                                min="0" 
                                                step="1000" 
                                                required
                                            >
                                            @error("products.{$index}.flash_price")
                                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input 
                                                type="number" 
                                                name="products[{{ $index }}][quantity_limit]" 
                                                value="{{ $qtyLimit }}" 
                                                class="form-control form-control-sm @error("products.{$index}.quantity_limit") is-invalid @enderror"
                                                min="{{ max(1, $soldQty) }}" 
                                                required
                                            >
                                            @error("products.{$index}.quantity_limit")
                                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td class="text-center">
                                            <span class="badge text-bg-light border fw-semibold">
                                                {{ number_format($soldQty) }}
                                            </span>
                                        </td>
                                        <td>
                                            <input 
                                                type="number" 
                                                name="products[{{ $index }}][limit_per_user]" 
                                                value="{{ $limitUser }}" 
                                                class="form-control form-control-sm"
                                                min="0"
                                                placeholder="0: Không hạn"
                                            >
                                        </td>
                                        <td class="text-center">
                                            @if($soldQty > 0)
                                                <button type="button" class="btn btn-sm btn-link text-body-secondary p-0" title="{{ __('Đã có lượt mua, không thể xóa') }}" disabled>
                                                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-row" title="{{ __('Xóa khỏi danh sách') }}">
                                                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="empty-products-placeholder">
                                        <td colspan="7" class="text-center py-5 text-body-secondary">
                                            <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="mx-auto mb-2 opacity-50">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                            <div>{{ __('Chưa có sản phẩm nào được chọn. Nhấn nút "Chọn sản phẩm" ở trên để thêm.') }}</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-admin.card>

            </div>

            {{-- SIDEBAR: col-md-4 col-lg-3 --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- Khung giờ --}}
                <x-admin.card :title="__('Khung giờ Flash Sale')">
                    <x-admin.form-group :label="__('Thời gian bắt đầu')" name="starts_at" required>
                        <x-admin.input 
                            type="datetime-local" 
                            name="starts_at" 
                            value="{{ old('starts_at', $flashSale?->starts_at?->format('Y-m-d\TH:i')) }}" 
                            :required="true"
                            :disabled="$isEdit && $flashSale->hasStarted()" 
                        />
                        @if($isEdit && $flashSale->hasStarted())
                            <small class="text-muted d-block mt-1">{{ __('Chiến dịch đã bắt đầu, không thể sửa giờ bắt đầu.') }}</small>
                        @endif
                    </x-admin.form-group>

                    <x-admin.form-group :label="__('Thời gian kết thúc')" name="ends_at" required>
                        <x-admin.input 
                            type="datetime-local" 
                            name="ends_at" 
                            value="{{ old('ends_at', $flashSale?->ends_at?->format('Y-m-d\TH:i')) }}" 
                            :required="true" 
                        />
                    </x-admin.form-group>
                </x-admin.card>

                {{-- Xuất bản & Thao tác --}}
                <x-admin.card :title="__('Xuất bản')">
                    <div class="mb-3">
                        <x-admin.toggle 
                            name="is_active" 
                            :label="__('Kích hoạt chiến dịch')" 
                            :checked="old('is_active', $flashSale?->is_active ?? true)" 
                        />
                        <small class="text-body-secondary d-block mt-1">
                            {{ __('Khi tắt, chiến dịch sẽ ở trạng thái Bản nháp và không mở bán trên Storefront.') }}
                        </small>
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="d-flex flex-column gap-2">
                        <x-admin.button type="submit" name="submit_action" value="save" variant="primary" class="w-100 justify-content-center">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>{{ __('Lưu thay đổi') }}</span>
                        </x-admin.button>

                        <x-admin.button type="submit" name="submit_action" value="save_and_edit" variant="secondary" class="w-100 justify-content-center">
                            <span>{{ __('Lưu & Tiếp tục sửa') }}</span>
                        </x-admin.button>

                        <x-admin.button href="{{ route('admin.flash-sales.index') }}" variant="outline-secondary" class="w-100 justify-content-center">
                            <span>{{ __('Quay lại danh sách') }}</span>
                        </x-admin.button>
                    </div>
                </x-admin.card>

            </div>

        </div>
    </form>

    {{-- Modal Chọn Sản phẩm --}}
    <x-admin.modal id="modal-select-products" :title="__('Chọn sản phẩm tham gia Flash Sale')" maxWidth="lg">
        <div class="p-3">
            <div class="mb-3">
                <input 
                    type="text" 
                    id="modal-product-search" 
                    class="form-control" 
                    placeholder="{{ __('Tìm theo tên sản phẩm hoặc SKU...') }}"
                >
            </div>

            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" id="modal-product-list-table">
                    <thead class="table-light small sticky-top">
                        <tr>
                            <th style="width: 40px;" class="text-center">#</th>
                            <th>{{ __('Sản phẩm / Biến thể') }}</th>
                            <th style="width: 140px;">{{ __('Giá bán') }}</th>
                            <th style="width: 100px;" class="text-center">{{ __('Tồn kho') }}</th>
                            <th style="width: 100px;" class="text-center">{{ __('Chọn') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $prod)
                            @if($prod->variants->isNotEmpty())
                                @foreach($prod->variants as $var)
                                    @php
                                        $skuKey = $prod->id . '-' . $var->id;
                                    @endphp
                                    <tr 
                                        class="modal-product-row" 
                                        data-product-id="{{ $prod->id }}"
                                        data-variant-id="{{ $var->id }}"
                                        data-product-name="{{ $prod->name }}"
                                        data-variant-name="{{ $var->name }}"
                                        data-sku="{{ $var->sku ?: $prod->sku }}"
                                        data-price="{{ (float)$var->price }}"
                                        data-key="{{ $skuKey }}"
                                    >
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input modal-row-check" value="{{ $skuKey }}">
                                        </td>
                                        <td>
                                            <div class="fw-medium text-body">{{ $prod->name }}</div>
                                            <div class="small text-primary fw-medium">{{ $var->name }}</div>
                                            <div class="small text-body-secondary font-monospace">{{ $var->sku ?: $prod->sku }}</div>
                                        </td>
                                        <td>
                                            <span class="small fw-semibold">{{ number_format($var->price, 0, ',', '.') }}đ</span>
                                        </td>
                                        <td class="text-center small">
                                            {{ number_format($var->stock_quantity) }}
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-add-single-product">
                                                {{ __('Thêm') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                @php
                                    $skuKey = $prod->id . '-0';
                                @endphp
                                <tr 
                                    class="modal-product-row" 
                                    data-product-id="{{ $prod->id }}"
                                    data-variant-id=""
                                    data-product-name="{{ $prod->name }}"
                                    data-variant-name=""
                                    data-sku="{{ $prod->sku }}"
                                    data-price="{{ (float)$prod->price }}"
                                    data-key="{{ $skuKey }}"
                                >
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input modal-row-check" value="{{ $skuKey }}">
                                    </td>
                                    <td>
                                        <div class="fw-medium text-body">{{ $prod->name }}</div>
                                        <div class="small text-body-secondary font-monospace">{{ $prod->sku }}</div>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold">{{ number_format($prod->price, 0, ',', '.') }}đ</span>
                                    </td>
                                    <td class="text-center small">
                                        {{ number_format($prod->stock_quantity) }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-add-single-product">
                                            {{ __('Thêm') }}
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <x-slot:footer>
            <x-admin.button type="button" variant="secondary" data-bs-dismiss="modal">
                {{ __('Đóng') }}
            </x-admin.button>
            <x-admin.button type="button" variant="primary" id="btn-add-selected-products">
                {{ __('Thêm các mục đã chọn') }}
            </x-admin.button>
        </x-slot:footer>
    </x-admin.modal>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var tbody = document.getElementById('flash-sale-products-tbody');
    var emptyPlaceholder = document.getElementById('empty-products-placeholder');
    var searchInput = document.getElementById('modal-product-search');
    var modalRows = document.querySelectorAll('.modal-product-row');

    function getNextIndex() {
        return tbody.querySelectorAll('tr[data-key]').length;
    }

    function checkEmptyState() {
        var hasRows = tbody.querySelectorAll('tr[data-key]').length > 0;
        if (hasRows && emptyPlaceholder) {
            emptyPlaceholder.classList.add('d-none');
        } else if (!hasRows && emptyPlaceholder) {
            emptyPlaceholder.classList.remove('d-none');
        }
    }

    function addProductToTable(prodData) {
        var existing = tbody.querySelector('tr[data-key="' + prodData.key + '"]');
        if (existing) {
            if (typeof AdminUI !== 'undefined') {
                AdminUI.notify('warning', 'Sản phẩm/biến thể này đã có trong danh sách!');
            }
            return;
        }

        var index = getNextIndex();
        var suggestedFlashPrice = Math.round(prodData.price * 0.8 / 1000) * 1000; // gợi ý giảm 20%
        if (suggestedFlashPrice >= prodData.price) {
            suggestedFlashPrice = Math.max(0, prodData.price - 10000);
        }

        var tr = document.createElement('tr');
        tr.setAttribute('data-key', prodData.key);
        tr.innerHTML = `
            <td>
                <input type="hidden" name="products[${index}][product_id]" value="${prodData.productId}">
                <input type="hidden" name="products[${index}][product_variant_id]" value="${prodData.variantId}">
                <input type="hidden" name="products[${index}][product_name]" value="${prodData.productName}">
                <input type="hidden" name="products[${index}][variant_name]" value="${prodData.variantName}">
                <input type="hidden" name="products[${index}][sku]" value="${prodData.sku}">
                <input type="hidden" name="products[${index}][regular_price]" value="${prodData.price}">
                <input type="hidden" name="products[${index}][sold_quantity]" value="0">
                <input type="hidden" name="products[${index}][sort_order]" value="${index}">

                <div class="fw-medium text-body">${prodData.productName}</div>
                ${prodData.variantName ? `<div class="small text-primary fw-medium">${prodData.variantName}</div>` : ''}
                ${prodData.sku ? `<div class="small text-body-secondary font-monospace">${prodData.sku}</div>` : ''}
            </td>
            <td>
                <span class="small text-body-secondary text-decoration-line-through">
                    ${new Intl.NumberFormat('vi-VN').format(prodData.price)}đ
                </span>
            </td>
            <td>
                <input 
                    type="number" 
                    name="products[${index}][flash_price]" 
                    value="${suggestedFlashPrice}" 
                    class="form-control form-control-sm"
                    min="0" 
                    step="1000" 
                    required
                >
            </td>
            <td>
                <input 
                    type="number" 
                    name="products[${index}][quantity_limit]" 
                    value="10" 
                    class="form-control form-control-sm"
                    min="1" 
                    required
                >
            </td>
            <td class="text-center">
                <span class="badge text-bg-light border fw-semibold">0</span>
            </td>
            <td>
                <input 
                    type="number" 
                    name="products[${index}][limit_per_user]" 
                    value="0" 
                    class="form-control form-control-sm"
                    min="0"
                    placeholder="0: Không hạn"
                >
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-row" title="Xóa">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </td>
        `;

        if (emptyPlaceholder) {
            emptyPlaceholder.classList.add('d-none');
        }
        tbody.appendChild(tr);

        if (typeof AdminUI !== 'undefined') {
            AdminUI.notify('success', 'Đã thêm ' + prodData.productName + (prodData.variantName ? ' (' + prodData.variantName + ')' : ''));
        }
    }

    // Xóa dòng
    tbody.addEventListener('click', function (e) {
        var removeBtn = e.target.closest('.btn-remove-row');
        if (removeBtn) {
            var row = removeBtn.closest('tr');
            if (row) {
                row.remove();
                checkEmptyState();
            }
        }
    });

    // Thêm 1 sản phẩm từ modal
    document.querySelectorAll('.btn-add-single-product').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var tr = btn.closest('.modal-product-row');
            addProductToTable({
                productId: tr.getAttribute('data-product-id'),
                variantId: tr.getAttribute('data-variant-id'),
                productName: tr.getAttribute('data-product-name'),
                variantName: tr.getAttribute('data-variant-name'),
                sku: tr.getAttribute('data-sku'),
                price: parseFloat(tr.getAttribute('data-price') || 0),
                key: tr.getAttribute('data-key'),
            });
        });
    });

    // Thêm nhiều sản phẩm đã check
    var btnAddSelected = document.getElementById('btn-add-selected-products');
    if (btnAddSelected) {
        btnAddSelected.addEventListener('click', function () {
            var checkedBoxes = document.querySelectorAll('.modal-row-check:checked');
            checkedBoxes.forEach(function (box) {
                var tr = box.closest('.modal-product-row');
                if (tr) {
                    addProductToTable({
                        productId: tr.getAttribute('data-product-id'),
                        variantId: tr.getAttribute('data-variant-id'),
                        productName: tr.getAttribute('data-product-name'),
                        variantName: tr.getAttribute('data-variant-name'),
                        sku: tr.getAttribute('data-sku'),
                        price: parseFloat(tr.getAttribute('data-price') || 0),
                        key: tr.getAttribute('data-key'),
                    });
                    box.checked = false;
                }
            });

            var modalEl = document.getElementById('modal-select-products');
            var modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }
        });
    }

    // Filter tìm kiếm trong modal
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var term = searchInput.value.toLowerCase().trim();
            modalRows.forEach(function (row) {
                var pName = (row.getAttribute('data-product-name') || '').toLowerCase();
                var vName = (row.getAttribute('data-variant-name') || '').toLowerCase();
                var sku = (row.getAttribute('data-sku') || '').toLowerCase();
                var match = pName.includes(term) || vName.includes(term) || sku.includes(term);
                row.classList.toggle('d-none', !match);
            });
        });
    }
});
</script>
@endpush