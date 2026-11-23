@extends('layouts.admin')

@php
    $block = $block ?? null;
    $isEdit = !is_null($block);
    $languages = $languages ?? $activeLanguages ?? collect();
    $defaultLocale = $defaultLocale ?? config('app.locale', 'vi');
    $currentLocale = $currentLocale ?? $defaultLocale;
    $currentLang = $languages->firstWhere('code', $currentLocale) ?? $languages->first();
    $translation = $isEdit ? $block->translations->firstWhere('locale', $currentLocale) : null;
    $actionParams = $isEdit ? ['uuid' => $block->uuid] : [];
    if ($currentLocale !== $defaultLocale) {
        $actionParams['lang'] = $currentLocale;
    }
    $actionUrl = $isEdit ? route('admin.content-blocks.update', $actionParams) : route('admin.content-blocks.store');
@endphp

@section('title', $isEdit ? __('Sửa Khối nội dung') : __('Thêm Khối nội dung mới'))

@section('content')
    <x-admin.page-header
        :title="$isEdit ? __('Sửa Khối nội dung') : __('Thêm Khối nội dung mới')"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Khối nội dung'), 'url' => route('admin.content-blocks.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')]
        ]"
    />

    <form id="content-block-form" action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data" data-unsaved-guard>
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="row align-items-start g-4">
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">
                @if($currentLang)
                    <div class="lang-panel d-flex flex-column gap-4" data-lang-panel="{{ $currentLocale }}">
                        <x-admin.card>
                            <x-admin.form-group label="{{ __('Tiêu đề khối') }}" name="translations.{{ $currentLocale }}.title" required>
                                <x-admin.input
                                    type="text"
                                    name="translations[{{ $currentLocale }}][title]"
                                    value="{{ old('translations.'.$currentLocale.'.title', $translation?->title ?? '') }}"
                                    placeholder="{{ __('Nhập tên khối nội dung...') }}"
                                    class="seo-source-name"
                                    required
                                />
                            </x-admin.form-group>

                            <x-admin.form-group
                                label="{{ __('Slug') }}"
                                name="translations.{{ $currentLocale }}.slug"
                                description="{{ __('Để trống hệ thống sẽ tự tạo từ tiêu đề.') }}"
                            >
                                <x-admin.input
                                    type="text"
                                    name="translations[{{ $currentLocale }}][slug]"
                                    value="{{ old('translations.'.$currentLocale.'.slug', $translation?->slug ?? '') }}"
                                    placeholder="noi-dung-gioi-thieu"
                                    class="seo-source-slug"
                                />
                            </x-admin.form-group>

                            <x-admin.form-group label="{{ __('Mô tả ngắn') }}" name="translations.{{ $currentLocale }}.excerpt">
                                <x-admin.textarea
                                    name="translations[{{ $currentLocale }}][excerpt]"
                                    rows="3"
                                    class="seo-source-excerpt"
                                    placeholder="{{ __('Mô tả ngắn cho khối nội dung...') }}"
                                >{{ old('translations.'.$currentLocale.'.excerpt', $translation?->excerpt ?? '') }}</x-admin.textarea>
                            </x-admin.form-group>

                            <x-admin.form-group label="{{ __('Nội dung HTML') }}" name="translations.{{ $currentLocale }}.content" class="mb-0">
                                <x-admin.textarea
                                    name="translations[{{ $currentLocale }}][content]"
                                    rows="18"
                                    class="tinymce-editor"
                                    placeholder="{{ __('Soạn nội dung HTML của khối tại đây...') }}"
                                >{{ old('translations.'.$currentLocale.'.content', $translation?->content ?? '') }}</x-admin.textarea>
                            </x-admin.form-group>
                        </x-admin.card>
                    </div>
                @else
                    <x-admin.card>
                        <div class="alert alert-warning mb-0">{{ __('Chưa có ngôn ngữ hoạt động. Vui lòng thêm ngôn ngữ trước khi tạo khối.') }}</div>
                    </x-admin.card>
                @endif
            </div>

            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">
                <x-admin.publish-box
                    :statuses="$statuses ?? []"
                    :status="$block?->status"
                    :published-at="$block?->published_at"
                    :default-now="!$isEdit"
                    :index-route="route('admin.content-blocks.index')"
                    :delete-route="$isEdit ? route('admin.content-blocks.destroy', $block->uuid) : null"
                />

                @if($languages->isNotEmpty())
                    <x-admin.language-switcher-widget
                        :languages="$languages"
                        :current-locale="$currentLocale"
                        :translations="$isEdit ? $block->translations : collect()"
                        route-prefix="admin.content-blocks"
                        :uuid="$isEdit ? $block->uuid : null"
                        :is-edit="$isEdit"
                    />
                @endif

                <x-admin.card title="{{ __('Ảnh đại diện') }}">
                    <x-admin.image-upload
                        name="image"
                        return="media-id"
                        :current="$block?->imageMedia?->getUrl()"
                        :current-id="$block?->image"
                        shape="wide"
                        description="{{ __('Ảnh tùy chọn cho khối nội dung.') }}"
                    />
                </x-admin.card>

                @if($isEdit)
                    <x-admin.card title="{{ __('Shortcode') }}">
                        <p class="small text-body-secondary">{{ __('Sao chép mã này để dùng trong nội dung khi hệ thống bật trình phân tích shortcode.') }}</p>
                        <code class="d-block small user-select-all bg-body-tertiary rounded p-2">{{ $block->shortcode }}</code>
                    </x-admin.card>
                @endif
            </div>
        </div>
    </form>

    <x-admin.scripts.auto-slug :is-edit="$isEdit" />
    <x-admin.scripts.tinymce />
    <x-admin.scripts.unsaved-guard />
@endsection
