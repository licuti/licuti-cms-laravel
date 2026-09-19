@extends('layouts.admin')

@php
    $product = $product ?? null;
    $isEdit = !is_null($product);
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
            {{-- CỘT TRÁI: NỘI DUNG SẢN PHẨM, GIÁ & KHO HÀNG, SEO         --}}
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

                {{-- CARD 3: GIÁ BÁN & KHO HÀNG --}}
                <x-admin.card :title="__('Giá bán & Quản lý tồn kho')">
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
                            <x-admin.form-group :label="__('Giá vốn (đ)')" name="cost_price" class="mb-0">
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
                                    value="{{ old('stock_quantity', $product?->stock_quantity ?? 10) }}" 
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

                {{-- CARD 4: HÌNH ẢNH SẢN PHẨM --}}
                <x-admin.card :title="__('Ảnh đại diện sản phẩm')">
                    @php
                        $primaryImg = $product?->primaryImage ?? $product?->images->first();
                        $currentImgUrl = $primaryImg?->url;
                        $currentUuid = old('images.0.image', $primaryImg?->image ?? null);
                    @endphp
                    <div class="mb-0">
                        <x-admin.image-upload 
                            name="images[0][image]" 
                            :current="$currentImgUrl" 
                            :current-uuid="$currentUuid" 
                            shape="square" 
                            description="{{ __('Khuyên dùng ảnh tỷ lệ vuông 800x800px hoặc 1000x1000px. Định dạng: JPG, PNG, WEBP.') }}" 
                        />
                        <input type="hidden" name="images[0][is_primary]" value="1">
                    </div>
                </x-admin.card>

            </div>

            {{-- ======================================================== --}}
            {{-- CỘT PHẢI: CÀI ĐẶT SẢN PHẨM (SIDEBAR)                     --}}
            {{-- ======================================================== --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- HỘP 1: XUẤT BẢN --}}
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

                {{-- HỘP 2: DANH MỤC SẢN PHẨM --}}
                <x-admin.card :title="__('Danh mục sản phẩm')">
                    <x-admin.form-group class="mb-0" name="category_id">
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">{{ __('--- Chọn danh mục ---') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product?->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </x-admin.form-group>
                </x-admin.card>

                {{-- HỘP 3: THƯƠNG HIỆU --}}
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

                {{-- HỘP 4: THÔNG SỐ VẬN CHUYỂN --}}
                <x-admin.card :title="__('Vận chuyển & Đóng gói')">
                    <x-admin.form-group :label="__('Khối lượng (kg)')" name="weight" class="mb-3">
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

                    <x-admin.form-group :label="__('Kích thước DxRxC (cm)')" name="dimensions" class="mb-0">
                        <x-admin.input 
                            type="text" 
                            name="dimensions" 
                            size="sm" 
                            value="{{ old('dimensions', $product?->dimensions ?? '') }}" 
                            placeholder="20x15x10" 
                        />
                    </x-admin.form-group>
                </x-admin.card>

            </div>
        </div>
    </form>

    <x-admin.scripts.auto-slug :is-edit="$isEdit" />
    <x-admin.scripts.tinymce />
@endsection