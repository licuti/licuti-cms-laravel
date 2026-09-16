@extends('layouts.admin')

@php
    $page = $page ?? null;
    $isEdit = !is_null($page);
@endphp

@section('title', $isEdit ? 'Sửa Trang tĩnh' : 'Thêm Trang tĩnh mới')

@section('content')
    {{-- Tiêu đề trang --}}
    <x-admin.page-header
        :title="$isEdit ? 'Sửa Trang tĩnh' : 'Thêm Trang tĩnh mới'"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'],
            ['label' => 'Trang tĩnh', 'url' => route('admin.pages.index')],
            ['label' => $isEdit ? 'Sửa' : 'Thêm mới']
        ]"
    />

    {{-- Form soạn thảo chính --}}
    <form
        id="page-form"
        action="{{ $isEdit ? route('admin.pages.update', $page->uuid) : route('admin.pages.store') }}"
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
                @if(isset($activeLanguages) && $activeLanguages->count() > 1)
                    <x-admin.lang-tabs :active-languages="$activeLanguages" :default-locale="$defaultLocale" />
                @endif

                @foreach($activeLanguages as $lang)
                    @php
                        $code = $lang->code ?? app()->getLocale();
                        $panelClass = ($code === $defaultLocale) ? '' : 'd-none';
                    @endphp

                    <div class="lang-panel {{ $panelClass }} d-flex flex-column gap-4" data-lang-panel="{{ $code }}">

                        {{-- CARD 1: NỘI DUNG TRANG --}}
                        <x-admin.card title="Nội dung trang">
                            {{-- Tiêu đề trang --}}
                            <x-admin.form-group
                                label="Tiêu đề trang"
                                name="translations.{{ $code }}.title"
                                :required="$code === $defaultLocale"
                            >
                                <x-admin.input
                                    type="text"
                                    name="translations[{{ $code }}][title]"
                                    size="sm"
                                    value="{{ old('translations.'.$code.'.title', $page?->translate($code)?->title ?? '') }}"
                                    placeholder="Nhập tiêu đề trang..."
                                    class="seo-source-name"
                                />
                            </x-admin.form-group>

                            {{-- Liên kết tĩnh (Slug) --}}
                            <x-admin.form-group
                                label="Liên kết tĩnh (Slug)"
                                name="translations.{{ $code }}.slug"
                                description="Để trống hệ thống sẽ tự động tạo từ tiêu đề."
                            >
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-body-secondary text-body-secondary">{{ url('/') }}/</span>
                                    <x-admin.input
                                        type="text"
                                        name="translations[{{ $code }}][slug]"
                                        size="sm"
                                        value="{{ old('translations.'.$code.'.slug', $page?->translate($code)?->slug ?? '') }}"
                                        placeholder="ve-chung-toi"
                                        class="seo-source-slug"
                                    />
                                </div>
                            </x-admin.form-group>

                            {{-- Mô tả ngắn (Excerpt) --}}
                            <x-admin.form-group
                                label="Mô tả ngắn"
                                name="translations.{{ $code }}.excerpt"
                                description="Đoạn mô tả ngắn hiển thị ở danh sách hoặc khi chia sẻ mạng xã hội."
                            >
                                <x-admin.textarea
                                    name="translations[{{ $code }}][excerpt]"
                                    size="sm"
                                    rows="3"
                                    class="seo-source-excerpt"
                                >{{ old('translations.'.$code.'.excerpt', $page?->translate($code)?->excerpt ?? '') }}</x-admin.textarea>
                            </x-admin.form-group>

                            {{-- Nội dung chi tiết (TinyMCE) --}}
                            <x-admin.form-group
                                label="Nội dung chi tiết"
                                name="translations.{{ $code }}.content"
                                class="mb-0"
                            >
                                <x-admin.textarea
                                    name="translations[{{ $code }}][content]"
                                    rows="16"
                                    class="tinymce-editor"
                                    placeholder="Bắt đầu viết nội dung trang ở đây..."
                                >{{ old('translations.'.$code.'.content', $page?->translate($code)?->content ?? '') }}</x-admin.textarea>
                            </x-admin.form-group>
                        </x-admin.card>

                        {{-- CARD 2: TỐI ƯU SEO --}}
                        <x-admin.card title="Tối ưu hóa Công cụ Tìm kiếm (SEO)">
                            <x-admin.seo-meta
                                :lang="$lang"
                                :seo="$page?->seoForLocale($code)"
                                :show-header="false"
                            />
                        </x-admin.card>

                    </div>
                @endforeach

            </div>

            {{-- ======================================================== --}}
            {{-- CỘT PHẢI: CÀI ĐẶT TRANG (SIDEBAR)                        --}}
            {{-- ======================================================== --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- HỘP 1: XUẤT BẢN --}}
                <x-admin.publish-box
                    :statuses="$statuses ?? []"
                    :status="$page?->status"
                    :published-at="$page?->published_at"
                    :default-now="!$isEdit"
                    :index-route="route('admin.pages.index')"
                />

                {{-- HỘP 2: THUỘC TÍNH TRANG --}}
                <x-admin.card title="Thuộc tính trang">
                    {{-- Trang cha --}}
                    <x-admin.form-group
                        label="Trang cha"
                        name="parent_id"
                        description="Chọn trang cha (nếu là con của một trang khác)."
                    >
                        <select name="parent_id" class="form-select form-select-sm">
                            <option value="">— Không có trang cha —</option>
                            @include('components.admin.partials.tree-select-options', [
                                'nodes'         => $pageTree ?? collect(),
                                'depth'         => 0,
                                'selectedValue' => (int) old('parent_id', $page?->parent_id ?? 0),
                            ])
                        </select>
                    </x-admin.form-group>

                    {{-- Mẫu trang --}}
                    <x-admin.form-group
                        label="Mẫu trang"
                        name="page_template"
                        description="Cách thức hiển thị của trang này."
                    >
                        <x-admin.select name="page_template" size="sm">
                            @foreach(($templates ?? []) as $val => $lbl)
                                <option value="{{ $val }}" @selected(old('page_template', $page?->page_template?->value ?? 'default') === $val)>
                                    {{ $lbl }}
                                </option>
                            @endforeach
                        </x-admin.select>
                    </x-admin.form-group>

                    {{-- Thứ tự hiển thị --}}
                    <x-admin.form-group
                        label="Thứ tự hiển thị"
                        name="display_order"
                        description="Số nhỏ hơn hiển thị trước."
                        class="mb-0"
                    >
                        <x-admin.input
                            type="number"
                            name="display_order"
                            size="sm"
                            min="0"
                            value="{{ old('display_order', $page?->display_order ?? 0) }}"
                        />
                    </x-admin.form-group>
                </x-admin.card>

                {{-- HỘP 3: ẢNH ĐẠI DIỆN --}}
                <x-admin.card title="Ảnh đại diện">
                    @php
                        $currentImgUrl = $page?->image_url ?? ($page?->image && !preg_match('/^[0-9a-f-]{36}$/i', $page->image) ? asset('storage/' . $page->image) : null);
                        $currentUuid = old('image_uuid', $page?->imageMedia?->uuid ?? ($page?->image && preg_match('/^[0-9a-f-]{36}$/i', $page->image) ? $page->image : null));
                    @endphp
                    <div class="mb-0">
                        <x-admin.image-upload
                            name="image"
                            :current="$currentImgUrl"
                            :current-uuid="$currentUuid"
                            shape="wide"
                            description="Khuyên dùng tỷ lệ 16:9. Định dạng JPEG, PNG, WEBP."
                        />
                    </div>
                </x-admin.card>

            </div>
        </div>
    </form>

    <x-admin.scripts.auto-slug :is-edit="$isEdit" />
    <x-admin.scripts.tinymce />
@endsection