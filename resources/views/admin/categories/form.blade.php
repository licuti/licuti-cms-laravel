@extends('layouts.admin')

@php
    $pageCategory = $category ?? null;
    $isEdit = !is_null($pageCategory);
    $defaultLocale = optional($languages->firstWhere('is_default', true))->code
        ?? optional($languages->first())->code
        ?? app()->getLocale();
        
    $actionUrl = $isEdit
        ? route('admin.categories.update', $pageCategory->uuid ?? $pageCategory->id)
        : route('admin.categories.store');
@endphp

@section('title', $isEdit ? __('Sửa Danh mục: :name', ['name' => $pageCategory->translated_name]) : __('Thêm Danh mục mới'))

@section('content')
    {{-- Tiêu đề trang --}}
    <x-admin.page-header
        :title="$isEdit ? __('Sửa Danh mục: :name', ['name' => $pageCategory->translated_name]) : __('Thêm Danh mục mới')"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Danh mục sản phẩm'), 'url' => route('admin.categories.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')],
        ]"
    />

    {{-- Form soạn thảo chính --}}
    <form
        id="category-form"
        action="{{ $actionUrl }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row align-items-start g-4">

            {{-- ======================================================== --}}
            {{-- CỘT TRÁI: NỘI DUNG SOẠN THẢO VÀ SEO                     --}}
            {{-- ======================================================== --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">

                {{-- Tabs chọn ngôn ngữ --}}
                @if(isset($languages) && $languages->count() > 1)
                    <x-admin.lang-tabs :active-languages="$languages" :default-locale="$defaultLocale" />
                @endif

                @foreach($languages as $lang)
                    @php
                        $code = $lang->code ?? app()->getLocale();
                        $panelClass = ($code === $defaultLocale) ? '' : 'd-none';
                        $trans = $isEdit ? $pageCategory->translations->firstWhere('locale', $code) : null;
                    @endphp

                    <div class="lang-panel {{ $panelClass }} d-flex flex-column gap-4" data-lang-panel="{{ $code }}">

                        {{-- CARD 1: NỘI DUNG DANH MỤC --}}
                        <x-admin.card title="{{ __('Nội dung danh mục') }}">
                            {{-- Tên danh mục --}}
                            <x-admin.form-group
                                label="{{ __('Tên danh mục') }}"
                                name="translations.{{ $code }}.name"
                                :required="$code === $defaultLocale"
                            >
                                <x-admin.input
                                    type="text"
                                    name="translations[{{ $code }}][name]"
                                    size="sm"
                                    value="{{ old('translations.'.$code.'.name', $trans?->name ?? '') }}"
                                    placeholder="{{ __('Nhập tên danh mục...') }}"
                                    class="seo-source-name"
                                />
                            </x-admin.form-group>

                            {{-- Liên kết tĩnh (Slug) --}}
                            <x-admin.form-group
                                label="{{ __('Liên kết tĩnh (Slug)') }}"
                                name="translations.{{ $code }}.slug"
                                description="{{ __('Để trống hệ thống sẽ tự động tạo từ tên.') }}"
                            >
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-body-secondary text-body-secondary">{{ url('/categories') }}/</span>
                                    <x-admin.input
                                        type="text"
                                        name="translations[{{ $code }}][slug]"
                                        size="sm"
                                        value="{{ old('translations.'.$code.'.slug', $trans?->slug ?? '') }}"
                                        placeholder="danh-muc-san-pham"
                                        class="seo-source-slug"
                                    />
                                </div>
                            </x-admin.form-group>

                            {{-- Mô tả ngắn --}}
                            <x-admin.form-group
                                label="{{ __('Mô tả') }}"
                                name="translations.{{ $code }}.description"
                                description="{{ __('Mô tả ngắn gọn về danh mục sản phẩm.') }}"
                            >
                                <x-admin.textarea
                                    name="translations[{{ $code }}][description]"
                                    size="sm"
                                    rows="3"
                                    class="seo-source-excerpt"
                                    placeholder="{{ __('Nhập đoạn mô tả ngắn...') }}"
                                >{{ old('translations.'.$code.'.description', $trans?->description ?? '') }}</x-admin.textarea>
                            </x-admin.form-group>

                            {{-- Nội dung chi tiết (TinyMCE) --}}
                            <x-admin.form-group
                                label="{{ __('Nội dung chi tiết') }}"
                                name="translations.{{ $code }}.content"
                                class="mb-0"
                            >
                                <x-admin.textarea
                                    name="translations[{{ $code }}][content]"
                                    rows="12"
                                    class="tinymce-editor"
                                    placeholder="{{ __('Soạn thảo nội dung mô tả chi tiết danh mục...') }}"
                                >{{ old('translations.'.$code.'.content', $trans?->content ?? '') }}</x-admin.textarea>
                            </x-admin.form-group>
                        </x-admin.card>

                        {{-- CARD 2: TỐI ƯU SEO --}}
                        <x-admin.card title="{{ __('Tối ưu hóa Công cụ Tìm kiếm (SEO)') }}">
                            <x-admin.seo-meta
                                :lang="$lang"
                                :seo="$pageCategory?->seoForLocale($code)"
                                :show-header="false"
                            />
                        </x-admin.card>

                    </div>
                @endforeach

            </div>

            {{-- ======================================================== --}}
            {{-- CỘT PHẢI: CÀI ĐẶT DANH MỤC (SIDEBAR)                     --}}
            {{-- ======================================================== --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- HỘP 1: XUẤT BẢN --}}
                <x-admin.publish-box
                    :statuses="['1' => __('Hoạt động'), '0' => __('Đã ẩn')]"
                    :status="$isEdit ? ($pageCategory->is_active ? '1' : '0') : '1'"
                    :show-published-at="false"
                    :index-route="route('admin.categories.index')"
                />

                {{-- HỘP 2: THUỘC TÍNH DANH MỤC --}}
                <x-admin.card title="{{ __('Thuộc tính danh mục') }}">
                    {{-- Danh mục cha --}}
                    <x-admin.form-group
                        label="{{ __('Danh mục cha') }}"
                        name="parent_id"
                        description="{{ __('Chọn danh mục cấp trên (nếu có).') }}"
                    >
                        <select name="parent_id" class="form-select form-select-sm">
                            <option value="">{{ __('— Không có danh mục cha —') }}</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}" @selected((string) old('parent_id', $pageCategory?->parent_id ?? '') === (string) $parent->id)>
                                    {{ $parent->tree_name ?? $parent->translated_name }}
                                </option>
                            @endforeach
                        </select>
                    </x-admin.form-group>

                    {{-- Thứ tự hiển thị --}}
                    <x-admin.form-group
                        label="{{ __('Thứ tự hiển thị') }}"
                        name="display_order"
                        description="{{ __('Số nhỏ hơn sẽ hiển thị trước.') }}"
                        class="mb-0"
                    >
                        <x-admin.input
                            type="number"
                            name="display_order"
                            size="sm"
                            min="0"
                            value="{{ old('display_order', $pageCategory?->display_order ?? 0) }}"
                        />
                    </x-admin.form-group>
                </x-admin.card>

                {{-- HỘP 3: ẢNH ĐẠI DIỆN --}}
                <x-admin.card title="{{ __('Ảnh đại diện') }}">
                    @php
                        $currentImgUrl = $pageCategory?->image_url;
                        $currentUuid = old('image_uuid', $pageCategory?->imageMedia?->uuid ?? ($pageCategory?->image && preg_match('/^[0-9a-f-]{36}$/i', $pageCategory->image) ? $pageCategory->image : null));
                    @endphp
                    <div class="mb-0">
                        <x-admin.image-upload
                            name="image"
                            :current="$currentImgUrl"
                            :current-uuid="$currentUuid"
                            shape="square"
                            description="{{ __('Khuyên dùng tỷ lệ 1:1 vuông. Định dạng: JPEG, PNG, WEBP.') }}"
                        />
                    </div>
                </x-admin.card>

            </div>
        </div>
    </form>

    <x-admin.scripts.auto-slug :is-edit="$isEdit" />
    <x-admin.scripts.tinymce />
@endsection
