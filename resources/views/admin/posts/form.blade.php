@extends('layouts.admin')

@php
    $post = $post ?? null;
    $isEdit = !is_null($post);
    $languages = $languages ?? $activeLanguages ?? collect();
    $defaultLocale = $defaultLocale ?? config('app.locale', 'vi');
    $currentLocale = $currentLocale ?? $defaultLocale;
    $currentLang = $languages->firstWhere('code', $currentLocale) ?? $languages->first();
    $translation = $isEdit ? $post->translations->firstWhere('locale', $currentLocale) : null;

    $actionParams = $isEdit ? ['uuid' => $post->uuid] : [];
    if ($currentLocale !== $defaultLocale) {
        $actionParams['lang'] = $currentLocale;
    }
    $actionUrl = $isEdit ? route('admin.posts.update', $actionParams) : route('admin.posts.store');
@endphp

@section('title', $isEdit ? 'Sửa Bài viết' : 'Thêm Bài viết mới')

@section('content')
    {{-- Tiêu đề trang --}}
    <x-admin.page-header 
        :title="$isEdit ? 'Sửa Bài viết' : 'Thêm Bài viết mới'" 
        subtitle=""
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'], 
            ['label' => 'Bài viết', 'url' => route('admin.posts.index')],
            ['label' => $isEdit ? 'Sửa' : 'Thêm mới']
        ]" 
    />

    {{-- Form soạn thảo chính --}}
    <form 
        id="post-form" 
        action="{{ $actionUrl }}" 
        method="POST" 
        enctype="multipart/form-data"
        data-unsaved-guard
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

                @if($currentLang)
                    <div class="lang-panel d-flex flex-column gap-4" data-lang-panel="{{ $currentLocale }}">

                        {{-- CARD 1: VIẾT BÀI VÀ TÓM TẮT --}}
                        <x-admin.card>
                            {{-- Tiêu đề bài viết --}}
                            <x-admin.form-group 
                                label="Tiêu đề bài viết" 
                                name="translations.{{ $currentLocale }}.title" 
                                required
                            >
                                <x-admin.input 
                                    type="text" 
                                    name="translations[{{ $currentLocale }}][title]" 
                                    value="{{ old('translations.'.$currentLocale.'.title', $translation?->title ?? '') }}" 
                                    placeholder="Nhập tiêu đề bài viết..." 
                                    class="seo-source-name"
                                    data-lang="{{ $currentLocale }}"
                                    required
                                />
                            </x-admin.form-group>

                            {{-- Liên kết tĩnh (Slug) — kiểu WordPress: hiển thị URL, bấm "Chỉnh sửa" mới sửa --}}
                            <x-admin.slug-field
                                :name="'translations['.$currentLocale.'][slug]'"
                                :error-name="'translations.'.$currentLocale.'.slug'"
                                :value="old('translations.'.$currentLocale.'.slug', $translation?->slug ?? '')"
                                :base-url="url('/posts')"
                                placeholder="bai-viet-moi"
                            />

                            {{-- Tóm tắt (Excerpt) --}}
                            <x-admin.form-group 
                                label="Tóm tắt (Excerpt)"
                                name="translations.{{ $currentLocale }}.excerpt" 
                                description=""
                                class="mb-0"
                            >
                                <x-admin.textarea 
                                    name="translations[{{ $currentLocale }}][excerpt]" 
                                    rows="3" 
                                    class="seo-source-excerpt"
                                    placeholder="Nhập đoạn tóm tắt bài viết..."
                                >{{ old('translations.'.$currentLocale.'.excerpt', $translation?->excerpt ?? '') }}</x-admin.textarea>
                            </x-admin.form-group>
                            
                            {{-- Nội dung bài viết (TinyMCE) --}}
                            <x-admin.form-group 
                                label="Nội dung bài viết" 
                                name="translations.{{ $currentLocale }}.content"
                            >
                                <x-admin.textarea 
                                    name="translations[{{ $currentLocale }}][content]" 
                                    rows="16" 
                                    class="tinymce-editor" 
                                    placeholder="Bắt đầu viết nội dung bài viết ở đây..."
                                >{{ old('translations.'.$currentLocale.'.content', $translation?->content ?? '') }}</x-admin.textarea>
                            </x-admin.form-group>

                        </x-admin.card>

                        {{-- CARD 2: TỐI ƯU SEO --}}
                        <x-admin.card title="Tối ưu hóa Công cụ Tìm kiếm (SEO)">
                            <x-admin.seo-meta 
                                :lang="$currentLang" 
                                :seo="$post?->seoForLocale($currentLocale)" 
                                :show-header="false"
                            />
                        </x-admin.card>

                    </div>
                @else
                    <x-admin.card>
                        <div class="alert alert-warning mb-0">
                            {{ __('Chưa có ngôn ngữ hoạt động. Vui lòng thêm ngôn ngữ tại') }}
                            <a href="{{ route('admin.languages.index') }}" class="fw-semibold">{{ __('Quản lý Ngôn ngữ') }}</a>
                            {{ __('trước khi soạn bài viết.') }}
                        </div>
                    </x-admin.card>
                @endif

            </div>

            {{-- ======================================================== --}}
            {{-- CỘT PHẢI: CÀI ĐẶT BÀI VIẾT (SIDEBAR)                     --}}
            {{-- ======================================================== --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- HỘP 1: XUẤT BẢN --}}
                <x-admin.publish-box
                    :statuses="$statuses ?? []"
                    :status="$post?->status"
                    :published-at="$post?->published_at"
                    :default-now="!$isEdit"
                    :show-featured="true"
                    :featured="(bool) old('is_featured', $post?->is_featured ?? false)"
                    featured-label="Nổi bật"
                    :index-route="route('admin.posts.index')"
                />

                {{-- HỘP 2: NGÔN NGỮ --}}
                @if($languages->isNotEmpty())
                    <x-admin.language-switcher-widget
                        :languages="$languages"
                        :current-locale="$currentLocale"
                        :translations="$isEdit ? $post->translations : collect()"
                        route-prefix="admin.posts"
                        :uuid="$isEdit ? $post->uuid : null"
                        :is-edit="$isEdit"
                    />
                @endif

                {{-- HỘP 3: CHUYÊN MỤC --}}
                <x-admin.card title="Chuyên mục">
                    <x-admin.form-group class="mb-0">
                        <x-admin.tree-checkbox 
                            name="category_ids" 
                            :options="$categories ?? []" 
                            :selected="old('category_ids', $post ? $post->categories->pluck('id')->toArray() : [])" 
                            maxHeight="250px"
                        />
                    </x-admin.form-group>
                </x-admin.card>

                {{-- HỘP 4: ẢNH ĐẠI DIỆN --}}
                <x-admin.card title="Ảnh đại diện">
                    @php
                        $currentMediaId = old('image_media_id', $post?->image);
                        $currentImgUrl = $post?->imageMedia?->getUrl();
                    @endphp
                    <div class="mb-0">
                        <x-admin.image-upload
                            name="image"
                            return="media-id"
                            :current="$currentImgUrl"
                            :current-id="$currentMediaId"
                            shape="wide"
                            description="Khuyên dùng tỷ lệ 16:9. Định dạng: JPEG, PNG, WEBP."
                        />
                    </div>
                </x-admin.card>

                {{-- HỘP 5: THÔNG TIN BÀI VIẾT --}}
                <x-admin.card title="Thông tin bài viết">
                    <x-admin.form-group label="Tác giả" name="author_id" class="mb-3">
                        <x-admin.select name="author_id">
                            @foreach($authors ?? [] as $author)
                                <option value="{{ $author->id }}" {{ old('author_id', $post?->author_id ?? auth()->id()) == $author->id ? 'selected' : '' }}>
                                    {{ $author->name }}
                                </option>
                            @endforeach
                        </x-admin.select>
                    </x-admin.form-group>

                    <x-admin.form-group label="Lượt xem" name="view_count" class="mb-0">
                        <x-admin.input
                            type="number"
                            name="view_count"
                            min="0"
                            value="{{ old('view_count', $post?->view_count ?? 0) }}"
                        />
                    </x-admin.form-group>
                </x-admin.card>

            </div>
        </div>
    </form>
<x-admin.scripts.auto-slug :is-edit="$isEdit" />
<x-admin.scripts.tinymce />
<x-admin.scripts.unsaved-guard />

@endsection
