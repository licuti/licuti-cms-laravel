@extends('layouts.admin')

@php
    $post = $post ?? null;
    $isEdit = !is_null($post);
@endphp

@section('title', $isEdit ? 'Sửa Bài viết' : 'Thêm Bài viết mới')

@section('content')
    {{-- Tiêu đề trang --}}
    <x-admin.page-header 
        :title="$isEdit ? 'Sửa Bài viết' : 'Thêm Bài viết mới'" 
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'], 
            ['label' => 'Bài viết', 'url' => route('admin.posts.index')],
            ['label' => $isEdit ? 'Sửa' : 'Thêm mới']
        ]" 
    />

    {{-- Form soạn thảo chính --}}
    <form 
        id="post-form" 
        action="{{ $isEdit ? route('admin.posts.update', $post->uuid) : route('admin.posts.store') }}" 
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
                        
                        {{-- CARD 1: VIẾT BÀI VÀ TÓM TẮT --}}
                        <x-admin.card>
                            {{-- Tiêu đề bài viết --}}
                            <x-admin.form-group 
                                label="Tiêu đề bài viết" 
                                name="translations.{{ $code }}.title" 
                                :required="$code === $defaultLocale"
                            >
                                <x-admin.input 
                                    type="text" 
                                    name="translations[{{ $code }}][title]" 
                                    size="sm"
                                    value="{{ old('translations.'.$code.'.title', $post?->translate($code)?->title ?? '') }}" 
                                    placeholder="Nhập tiêu đề bài viết..." 
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
                                    <span class="input-group-text bg-body-secondary text-body-secondary">{{ url('/posts') }}/</span>
                                    <x-admin.input 
                                        type="text" 
                                        name="translations[{{ $code }}][slug]" 
                                        size="sm" 
                                        value="{{ old('translations.'.$code.'.slug', $post?->translate($code)?->slug ?? '') }}" 
                                        placeholder="bai-viet-moi" 
                                        class="seo-source-slug" 
                                    />
                                </div>
                            </x-admin.form-group>

                            {{-- Nội dung bài viết (TinyMCE) --}}
                            <x-admin.form-group 
                                label="Nội dung bài viết" 
                                name="translations.{{ $code }}.content"
                            >
                                <x-admin.textarea 
                                    name="translations[{{ $code }}][content]" 
                                    rows="16" 
                                    class="tinymce-editor" 
                                    placeholder="Bắt đầu viết nội dung bài viết ở đây..."
                                >{{ old('translations.'.$code.'.content', $post?->translate($code)?->content ?? '') }}</x-admin.textarea>
                            </x-admin.form-group>

                            {{-- Tóm tắt (Excerpt) --}}
                            <x-admin.form-group 
                                label="Tóm tắt (Excerpt)"
                                name="translations.{{ $code }}.excerpt" 
                                description="Đoạn mô tả ngắn hiển thị ở danh mục tin tức và khi chia sẻ trên mạng xã hội."
                                class="mb-0"
                            >
                                <x-admin.textarea 
                                    name="translations[{{ $code }}][excerpt]" 
                                    size="sm" 
                                    rows="3" 
                                    class="seo-source-excerpt"
                                    placeholder="Nhập đoạn tóm tắt bài viết..."
                                >{{ old('translations.'.$code.'.excerpt', $post?->translate($code)?->excerpt ?? '') }}</x-admin.textarea>
                            </x-admin.form-group>
                        </x-admin.card>

                        {{-- CARD 2: TỐI ƯU SEO --}}
                        <x-admin.card title="Tối ưu hóa Công cụ Tìm kiếm (SEO)">
                            <x-admin.seo-meta 
                                :lang="$lang" 
                                :seo="$post?->seoForLocale($code)" 
                                :show-header="false"
                            />
                        </x-admin.card>

                    </div>
                @endforeach

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
                    featured-label="Đánh dấu bài viết nổi bật"
                    :index-route="route('admin.posts.index')"
                />

                {{-- HỘP 2: CHUYÊN MỤC --}}
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

                {{-- HỘP 3: ẢNH ĐẠI DIỆN --}}
                <x-admin.card title="Ảnh đại diện">
                    @php 
                        $thumbMedia = $post ? ($post->imageMedia ?? null) : null; 
                        $currentImgUrl = $post?->image_url ?? ($post?->image && !preg_match('/^[0-9a-f-]{36}$/i', $post->image) ? asset('storage/' . $post->image) : null);
                        $currentUuid = old('image_uuid', $thumbMedia?->uuid ?? ($post?->image && preg_match('/^[0-9a-f-]{36}$/i', $post->image) ? $post->image : null));
                    @endphp
                    <div class="mb-0">
                        <x-admin.image-upload 
                            name="image" 
                            :current="$currentImgUrl" 
                            :current-uuid="$currentUuid" 
                            shape="wide" 
                            description="Khuyên dùng tỷ lệ 16:9. Định dạng: JPEG, PNG, WEBP." 
                        />
                    </div>
                </x-admin.card>

                {{-- HỘP 4: THÔNG TIN BÀI VIẾT --}}
                <x-admin.card title="Thông tin bài viết">
                    <x-admin.form-group label="Tác giả" name="author_id" class="mb-3">
                        <x-admin.select name="author_id" size="sm">
                            @foreach($authors ?? [] as $author)
                                <option value="{{ $author->id }}" {{ old('author_id', $post?->author_id ?? auth()->id()) == $author->id ? 'selected' : '' }}>
                                    {{ $author->name }}
                                </option>
                            @endforeach
                        </x-admin.select>
                    </x-admin.form-group>

                    @if($isEdit)
                        <div class="small text-body-secondary mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between">
                                <span>Lượt xem:</span>
                                <span class="fw-medium text-body">{{ $post?->view_count ?? 0 }}</span>
                            </div>
                        </div>
                    @endif
                </x-admin.card>

            </div>
        </div>
    </form>
<x-admin.scripts.auto-slug :is-edit="$isEdit" />
<x-admin.scripts.tinymce />

@endsection