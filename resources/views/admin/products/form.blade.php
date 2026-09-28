@extends('layouts.admin')

@php
    $product = $product ?? null;
    $isEdit = !is_null($product);
    $productTypes = $productTypes ?? [];
    $tags = $tags ?? collect();
@endphp

@section('title', $isEdit ? __('Sửa Sản phẩm') : __('Thêm Sản phẩm mới'))

@section('content')
    {{-- Page Header --}}
    <x-admin.page-header 
        :title="$isEdit ? __('Sửa Sản phẩm') : __('Thêm Sản phẩm mới')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Sản phẩm'), 'url' => route('admin.products.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')]
        ]" 
    />

    {{-- Main Form --}}
    <form 
        id="product-form" 
        action="{{ $isEdit ? route('admin.products.update', $product->uuid) : route('admin.products.store') }}" 
        method="POST" 
        enctype="multipart/form-data"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row align-items-start g-4">

            {{-- ======================================================== --}}
            {{-- CỘT TRÁI: TAB NỘI DUNG                                   --}}
            {{-- ======================================================== --}}
            <div class="col-md-9 col-lg-9">

                <ul class="nav nav-tabs border-bottom mb-4" role="tablist" id="product-tabs">
                    <li class="nav-item">
                        <button type="button" class="nav-link fw-medium small active" data-product-tab="info" role="tab" aria-selected="true">
                            {{ __('Thông tin chung') }}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link fw-medium small text-body-secondary" data-product-tab="pricing" role="tab" aria-selected="false">
                            {{ __('Giá & Tồn kho') }}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link fw-medium small text-body-secondary" data-product-tab="shipping" role="tab" aria-selected="false">
                            {{ __('Vận chuyển & Thuế') }}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link fw-medium small text-body-secondary" data-product-tab="attributes" role="tab" aria-selected="false">
                            {{ __('Thuộc tính') }}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link fw-medium small text-body-secondary" data-product-tab="variants" role="tab" aria-selected="false">
                            {{ __('Biến thể') }}
                        </button>
                    </li>
                </ul>

                {{-- TAB: THÔNG TIN CHUNG --}}
                <div class="product-tab-panel d-flex flex-column gap-4" data-product-panel="info">

                    {{-- Tabs chọn ngôn ngữ --}}
                    @if(isset($activeLanguages) && $activeLanguages->count() > 1)
                        <x-admin.lang-tabs :active-languages="$activeLanguages" :default-locale="$defaultLocale" />
                    @endif

                    @foreach($activeLanguages as $lang)
                        @php 
                            $code = $lang->code ?? app()->getLocale(); 
                            $panelClass = ($code === $defaultLocale) ? '' : 'd-none'; 
                            $translation = $product?->translate($code);
                        @endphp

                        <div class="lang-panel {{ $panelClass }} d-flex flex-column gap-4" data-lang-panel="{{ $code }}">
                            
                            {{-- CARD 1: THÔNG TIN SẢN PHẨM --}}
                            <x-admin.card>
                                {{-- Tên sản phẩm --}}
                                <x-admin.form-group 
                                    :label="__('Tên sản phẩm')" 
                                    name="translations.{{ $code }}.name" 
                                    :required="$code === $defaultLocale"
                                >
                                    <x-admin.input 
                                        type="text" 
                                        name="translations[{{ $code }}][name]" 
                                        size="sm"
                                        value="{{ old('translations.'.$code.'.name', $translation?->name ?? '') }}" 
                                        placeholder="{{ __('Nhập tên sản phẩm...') }}" 
                                        class="seo-source-name"
                                        :required="$code === $defaultLocale"
                                    />
                                </x-admin.form-group>

                                {{-- Đường dẫn tĩnh (Slug) --}}
                                <x-admin.form-group 
                                    :label="__('Liên kết tĩnh (Slug)')" 
                                    name="translations.{{ $code }}.slug"
                                    :description="__('Để trống hệ thống sẽ tự động tạo từ tên sản phẩm.')"
                                >
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-body-secondary text-body-secondary">{{ url('/products') }}/</span>
                                        <x-admin.input 
                                            type="text" 
                                            name="translations[{{ $code }}][slug]" 
                                            size="sm" 
                                            value="{{ old('translations.'.$code.'.slug', $translation?->slug ?? '') }}" 
                                            placeholder="san-pham-moi" 
                                            class="seo-source-slug" 
                                        />
                                    </div>
                                </x-admin.form-group>

                                {{-- Mô tả ngắn --}}
                                <x-admin.form-group 
                                    :label="__('Mô tả ngắn')"
                                    name="translations.{{ $code }}.short_description" 
                                    :description="__('Đoạn tóm tắt hiển thị ở danh mục sản phẩm và khi chia sẻ liên kết.')"
                                >
                                    <x-admin.textarea 
                                        name="translations[{{ $code }}][short_description]" 
                                        size="sm" 
                                        rows="3" 
                                        class="seo-source-excerpt"
                                        placeholder="{{ __('Nhập tóm tắt sản phẩm...') }}"
                                    >{{ old('translations.'.$code.'.short_description', $translation?->short_description ?? '') }}</x-admin.textarea>
                                </x-admin.form-group>

                                {{-- Chi tiết sản phẩm --}}
                                <x-admin.form-group 
                                    :label="__('Chi tiết sản phẩm')"
                                    name="translations.{{ $code }}.description" 
                                    class="mb-0"
                                >
                                    <x-admin.textarea 
                                        name="translations[{{ $code }}][description]" 
                                        size="sm" 
                                        rows="8" 
                                        class="tinymce-editor"
                                        placeholder="{{ __('Nhập thông số kỹ thuật và bài viết giới thiệu chi tiết sản phẩm...') }}"
                                    >{{ old('translations.'.$code.'.description', $translation?->description ?? '') }}</x-admin.textarea>
                                </x-admin.form-group>
                            </x-admin.card>

                            {{-- CARD 2: TỐI ƯU SEO --}}
                            <x-admin.card :title="__('Tối ưu hóa Công cụ Tìm kiếm (SEO)')">
                                <x-admin.seo-meta 
                                    :lang="$lang" 
                                    :seo="$product?->seoForLocale($code)" 
                                    :show-header="false"
                                />
                            </x-admin.card>

                        </div>
                    @endforeach

                </div>

                {{-- TAB: GIÁ & TỒN KHO --}}
                <div class="product-tab-panel d-flex flex-column gap-4 d-none" data-product-panel="pricing">
                    <x-admin.card :title="__('Giá bán')">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <x-admin.form-group :label="__('Giá bán chính thức (đ)')" name="price" required class="mb-0">
                                    <x-admin.input 
                                        type="number" 
                                        name="price" 
                                        size="sm" 
                                        step="1000"
                                        min="0"
                                        value="{{ old('price', $product?->price ?? 0) }}" 
                                        required 
                                    />
                                </x-admin.form-group>
                            </div>
                            <div class="col-md-4">
                                <x-admin.form-group :label="__('Giá so sánh / Giá gốc (đ)')" name="compare_price" class="mb-0">
                                    <x-admin.input 
                                        type="number" 
                                        name="compare_price" 
                                        size="sm" 
                                        step="1000"
                                        min="0"
                                        value="{{ old('compare_price', $product?->compare_price ?? '') }}" 
                                        placeholder="0"
                                    />
                                </x-admin.form-group>
                            </div>
                            <div class="col-md-4">
                                <x-admin.form-group :label="__('Giá vốn (đ)')" name="cost_price" class="mb-0"
                                    :description="__('Dùng để tính biên lợi nhuận. Khách hàng không thấy giá này.')">
                                    <x-admin.input 
                                        type="number" 
                                        name="cost_price" 
                                        size="sm" 
                                        step="1000"
                                        min="0"
                                        value="{{ old('cost_price', $product?->cost_price ?? '') }}" 
                                        placeholder="0"
                                    />
                                </x-admin.form-group>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <x-admin.form-group :label="__('Mã SKU')" name="sku" class="mb-0">
                                    <x-admin.input 
                                        type="text" 
                                        name="sku" 
                                        size="sm" 
                                        value="{{ old('sku', $product?->sku ?? '') }}" 
                                        placeholder="PRD-001" 
                                    />
                                </x-admin.form-group>
                            </div>
                            <div class="col-md-4">
                                <x-admin.form-group :label="__('Mã vạch (Barcode / ISBN)')" name="barcode" class="mb-0">
                                    <x-admin.input 
                                        type="text" 
                                        name="barcode" 
                                        size="sm" 
                                        value="{{ old('barcode', $product?->barcode ?? '') }}" 
                                        placeholder="893..." 
                                    />
                                </x-admin.form-group>
                            </div>
                            <div class="col-md-4">
                                <x-admin.form-group :label="__('Số lượng trong kho')" name="stock_quantity" class="mb-0">
                                    <x-admin.input 
                                        type="number" 
                                        name="stock_quantity" 
                                        size="sm" 
                                        min="0"
                                        value="{{ old('stock_quantity', $product?->stock_quantity ?? '') }}" 
                                    />
                                </x-admin.form-group>
                            </div>
                        </div>

                        <div class="form-check form-switch mt-3 mb-0">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="track_inventory" 
                                   id="track_inventory" 
                                   value="1" 
                                   {{ old('track_inventory', $product?->track_inventory ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-medium" for="track_inventory">
                                {{ __('Tự động trừ số lượng tồn kho khi có đơn đặt hàng thành công') }}
                            </label>
                        </div>
                    </x-admin.card>

                    {{-- Chính sách tồn kho & khối lượng đặt hàng --}}
                    <x-admin.card :title="__('Chính sách tồn kho & Đặt hàng')">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <x-admin.form-group :label="__('Ngưỡng tồn thấp')" name="low_stock_threshold" class="mb-0"
                                    :description="__('Cảnh báo khi tồn kho xuống dưới mức này.')">
                                    <x-admin.input 
                                        type="number" 
                                        name="low_stock_threshold" 
                                        size="sm" 
                                        min="0"
                                        value="{{ old('low_stock_threshold', $product?->low_stock_threshold ?? '') }}" 
                                        placeholder="5" 
                                    />
                                </x-admin.form-group>
                            </div>
                            <div class="col-md-4">
                                <x-admin.form-group :label="__('Số lượng đặt tối thiểu')" name="min_order_quantity" class="mb-0">
                                    <x-admin.input 
                                        type="number" 
                                        name="min_order_quantity" 
                                        size="sm" 
                                        min="1"
                                        value="{{ old('min_order_quantity', $product?->min_order_quantity ?? '') }}" 
                                        placeholder="1" 
                                    />
                                </x-admin.form-group>
                            </div>
                            <div class="col-md-4">
                                <x-admin.form-group :label="__('Số lượng đặt tối đa')" name="max_order_quantity" class="mb-0">
                                    <x-admin.input 
                                        type="number" 
                                        name="max_order_quantity" 
                                        size="sm" 
                                        min="1"
                                        value="{{ old('max_order_quantity', $product?->max_order_quantity ?? '') }}" 
                                        placeholder="100" 
                                    />
                                </x-admin.form-group>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="allow_backorder" 
                                   id="allow_backorder" 
                                   value="1" 
                                   {{ old('allow_backorder', $product?->allow_backorder ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-medium" for="allow_backorder">
                                {{ __('Cho phép đặt hàng khi hết hàng (backorder)') }}
                            </label>
                        </div>

                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="sold_individually" 
                                   id="sold_individually" 
                                   value="1" 
                                   {{ old('sold_individually', $product?->sold_individually ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-medium" for="sold_individually">
                                {{ __('Chỉ bán 1 sản phẩm trong mỗi đơn hàng') }}
                            </label>
                        </div>
                    </x-admin.card>
                </div>

                {{-- TAB: VẬN CHUYỂN & THUẾ --}}
                <div class="product-tab-panel d-flex flex-column gap-4 d-none" data-product-panel="shipping">
                    <x-admin.card :title="__('Loại sản phẩm & Kích thước')">
                        <x-admin.form-group :label="__('Loại sản phẩm')" name="product_type">
                            <select name="product_type" id="product_type" class="form-select form-select-sm">
                                @foreach($productTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('product_type', $product?->product_type ?? 'physical') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </x-admin.form-group>

                        <div class="row g-3 shipping-fields">
                            <div class="col-md-3">
                                <x-admin.form-group :label="__('Khối lượng (kg)')" name="weight" class="mb-0">
                                    <x-admin.input 
                                        type="number" 
                                        name="weight" 
                                        size="sm" 
                                        step="0.01" 
                                        min="0"
                                        value="{{ old('weight', $product?->weight ?? '') }}" 
                                        placeholder="0.5" 
                                    />
                                </x-admin.form-group>
                            </div>
                            <div class="col-md-3">
                                <x-admin.form-group :label="__('Chiều dài (cm)')" name="length" class="mb-0">
                                    <x-admin.input 
                                        type="number" 
                                        name="length" 
                                        size="sm" 
                                        step="0.01" 
                                        min="0"
                                        value="{{ old('length', $product?->length ?? '') }}" 
                                        placeholder="20" 
                                    />
                                </x-admin.form-group>
                            </div>
                            <div class="col-md-3">
                                <x-admin.form-group :label="__('Chiều rộng (cm)')" name="width" class="mb-0">
                                    <x-admin.input 
                                        type="number" 
                                        name="width" 
                                        size="sm" 
                                        step="0.01" 
                                        min="0"
                                        value="{{ old('width', $product?->width ?? '') }}" 
                                        placeholder="15" 
                                    />
                                </x-admin.form-group>
                            </div>
                            <div class="col-md-3">
                                <x-admin.form-group :label="__('Chiều cao (cm)')" name="height" class="mb-0">
                                    <x-admin.input 
                                        type="number" 
                                        name="height" 
                                        size="sm" 
                                        step="0.01" 
                                        min="0"
                                        value="{{ old('height', $product?->height ?? '') }}" 
                                        placeholder="10" 
                                    />
                                </x-admin.form-group>
                            </div>
                        </div>

                        @if(!empty($product?->dimensions))
                            <div class="form-text mt-2">
                                {{ __('Kích thước cũ (:old) đã được tách thành 3 trường riêng.', ['old' => $product->dimensions]) }}
                            </div>
                        @endif
                    </x-admin.card>

                    <x-admin.card :title="__('Cước vận chuyển')">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="is_free_shipping" 
                                   id="is_free_shipping" 
                                   value="1" 
                                   {{ old('is_free_shipping', $product?->is_free_shipping ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-medium" for="is_free_shipping">
                                {{ __('Miễn phí vận chuyển') }}
                            </label>
                        </div>

                        <x-admin.form-group :label="__('Phí vận chuyển cố định (đ)')" name="shipping_fee" class="mb-0"
                            :description="__('Để trống để dùng phí mặc định theo khu vực. Bỏ qua khi đã chọn miễn phí vận chuyển.')">
                            <x-admin.input 
                                type="number" 
                                name="shipping_fee" 
                                size="sm" 
                                step="1000"
                                min="0"
                                value="{{ old('shipping_fee', $product?->shipping_fee ?? '') }}" 
                                placeholder="30000" 
                            />
                        </x-admin.form-group>
                    </x-admin.card>

                    <x-admin.card :title="__('Thuế')">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <x-admin.form-group :label="__('Thuế suất VAT (%)')" name="tax_rate" class="mb-0">
                                    <x-admin.input 
                                        type="number" 
                                        name="tax_rate" 
                                        size="sm" 
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        value="{{ old('tax_rate', $product?->tax_rate ?? '') }}" 
                                        placeholder="10" 
                                    />
                                </x-admin.form-group>
                            </div>
                            <div class="col-md-8">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="is_tax_inclusive" 
                                           id="is_tax_inclusive" 
                                           value="1" 
                                           {{ old('is_tax_inclusive', $product?->is_tax_inclusive ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-medium" for="is_tax_inclusive">
                                        {{ __('Giá bán đã bao gồm thuế') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </x-admin.card>
                </div>

                {{-- TAB: THUỘC TÍNH --}}
                <div class="product-tab-panel d-flex flex-column gap-4 d-none" data-product-panel="attributes">
                    <x-admin.card :title="__('Thuộc tính sản phẩm')">
                        <x-admin.product-attributes
                            :product="$product"
                            :catalog-attributes="$catalogAttributes ?? []"
                            :default-locale="$defaultLocale"
                            :store-route="isset($product) ? route('admin.products.attributes.store', $product->uuid) : null"
                        />
                    </x-admin.card>
                </div>

                {{-- TAB: BIẾN THỂ --}}
                <div class="product-tab-panel d-flex flex-column gap-4 d-none" data-product-panel="variants">
                    <x-admin.card :title="__('Biến thể sản phẩm')">
                        <x-admin.product-variants
                            :product="$product"
                            :default-price="old('price', $product?->price ?? 0)"
                            :default-stock="old('stock_quantity', $product?->stock_quantity ?? 0)"
                        />
                    </x-admin.card>
                </div>

            </div>

            {{-- ======================================================== --}}
            {{-- CỘT PHẢI: CÀI ĐẶT (SIDEBAR)                               --}}
            {{-- ======================================================== --}}
            <div class="col-md-3 col-lg-3 d-flex flex-column gap-4">

                {{-- HỘP 1: HÌNH ẢNH SẢN PHẨM --}}
                <x-admin.card :title="__('Hình ảnh sản phẩm')">
                    @php
                        $oldPrimaryUuid = old('primary_image_uuid');
                        $primaryImageUuid = $oldPrimaryUuid ?? $product?->primary_image;

                        if ($oldPrimaryUuid) {
                            $primaryImageUrl = filter_var($oldPrimaryUuid, FILTER_VALIDATE_URL)
                                ? $oldPrimaryUuid
                                : \App\Models\Media::where('uuid', $oldPrimaryUuid)->first()?->getUrl();
                        } else {
                            $primaryImageUrl = $product?->primary_image_url;
                        }
                    @endphp

                    <x-admin.image-upload
                        name="primary_image"
                        label="{{ __('Ảnh đại diện') }}"
                        :current="$primaryImageUrl"
                        :current-uuid="$primaryImageUuid"
                        description="{{ __('Ảnh đại diện hiển thị ở danh sách sản phẩm và khi chia sẻ. Nên dùng ảnh vuông 800x800px.') }}"
                    />

                    <hr class="my-3">

                    <p class="fw-semibold small mb-2">{{ __('Album ảnh') }}</p>
                    <x-admin.image-gallery
                        name="images"
                        :images="$product?->images"
                        :show-primary="false"
                        :item-width="120"
                        description="{{ __('Album ảnh chi tiết của sản phẩm. Định dạng: JPG, PNG, WEBP.') }}"
                    />
                </x-admin.card>

                {{-- HỘP 2: XUẤT BẢN --}}
                <x-admin.publish-box
                    :statuses="$statuses ?? []"
                    :status="$product?->status"
                    :published-at="$product?->published_at"
                    :default-now="!$isEdit"
                    :show-featured="true"
                    :featured="(bool) old('is_featured', $product?->is_featured ?? false)"
                    featured-label="{{ __('Sản phẩm nổi bật') }}"
                    :index-route="route('admin.products.index')"
                />

                {{-- HỘP 3: DANH MỤC SẢN PHẨM --}}
                <x-admin.card :title="__('Danh mục sản phẩm')">
                    <x-admin.form-group class="mb-0" name="category_id">
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">{{ __('--- Chọn danh mục ---') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product?->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->translated_name }}
                                </option>
                            @endforeach
                        </select>
                    </x-admin.form-group>
                </x-admin.card>

                {{-- HỘP 4: THƯƠNG HIỆU --}}
                <x-admin.card :title="__('Thương hiệu')">
                    <x-admin.form-group class="mb-0" name="brand_id">
                        <select name="brand_id" class="form-select form-select-sm">
                            <option value="">{{ __('--- Chọn thương hiệu ---') }}</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product?->brand_id) == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </x-admin.form-group>
                </x-admin.card>

                {{-- HỘP 5: THẺ TAG --}}
                <x-admin.card :title="__('Thẻ tag')">
                    @php
                        // old() có thể chứa id (tag sẵn có) hoặc text (tag vừa gõ mới)
                        $selectedTags = old('tags', $product?->tags?->pluck('id')?->all() ?? []);
                        $selectedTags = array_map('strval', (array) $selectedTags);
                    @endphp

                    <x-admin.form-group class="mb-0" name="tags"
                        :description="__('Gõ để tìm tag hoặc tạo tag mới.')">
                        <select name="tags[]" id="product-tags" class="product-tags-select" multiple></select>
                    </x-admin.form-group>
                </x-admin.card>

            </div>
        </div>
    </form>

    <x-admin.scripts.auto-slug :is-edit="$isEdit" />
    <x-admin.scripts.tinymce />

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ---- Tab chuyển đổi section form -------------------------------
        var tabBtns = document.querySelectorAll('[data-product-tab]');
        var panels = document.querySelectorAll('.product-tab-panel');

        tabBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var key = btn.getAttribute('data-product-tab');

                tabBtns.forEach(function (b) {
                    var isActive = b === btn;
                    b.classList.toggle('active', isActive);
                    b.classList.toggle('text-body-secondary', !isActive);
                    b.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                panels.forEach(function (panel) {
                    panel.classList.toggle('d-none', panel.getAttribute('data-product-panel') !== key);
                });
            });
        });

        // ---- Ẩn/hiện nhóm vận chuyển theo loại sản phẩm ----------------
        var typeSelect = document.getElementById('product_type');
        var shippingFields = document.querySelectorAll('[data-product-panel="shipping"] .shipping-fields');

        function toggleShippingFields() {
            if (!typeSelect) return;
            var needsShipping = typeSelect.value === 'physical';
            shippingFields.forEach(function (el) {
                el.classList.toggle('d-none', !needsShipping);
            });
        }

        if (typeSelect) {
            typeSelect.addEventListener('change', toggleShippingFields);
            toggleShippingFields();
        }

        // ---- Tags: tom-select multi + create ---------------------------
        var tagSelect = document.getElementById('product-tags');
        if (tagSelect && window.TomSelect && !tagSelect.dataset.tomSelectInitialized) {
            tagSelect.dataset.tomSelectInitialized = '1';

            // Danh sách tag sẵn có làm source gợi ý
            var tagOptions = @json($tags->map(fn ($t) => ['value' => (string) $t->id, 'text' => $t->name]));

            // Tag đang chọn: id số từ DB, hoặc text tag mới (từ old() khi validate fail)
            var selectedTags = @json($selectedTags);

            new window.TomSelect(tagSelect, {
                plugins: ['remove_button', 'clear_button'],
                create: true,
                createOnBlur: true,
                maxItems: null,
                hideSelected: true,
                selectOnTab: true,
                options: tagOptions,
                items: selectedTags,
                render: {
                    option: function (data, escape) {
                        return '<div><span>' + escape(data.text) + '</span></div>';
                    },
                    item: function (data, escape) {
                        return '<div><span>' + escape(data.text) + '</span></div>';
                    }
                }
            });
        }
    });
    </script>
    @endpush
@endsection
