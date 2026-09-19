@extends('layouts.admin')

@php
    $isEdit = isset($postCategory);
    $postCategory = $postCategory ?? null;
    $defaultLocale = optional($languages->firstWhere('is_default', true))->code
        ?? optional($languages->first())->code
        ?? config('app.locale', 'vi');

    $actionUrl = $isEdit
        ? route('admin.post-categories.update', ['uuid' => $postCategory->uuid ?? $postCategory->id, 'lang' => $currentLocale])
        : route('admin.post-categories.store', ['lang' => $currentLocale]);
@endphp

@section('title', $isEdit ? __('Sửa Danh mục Bài viết: :name', ['name' => $postCategory->translated_name]) : __('Thêm Danh mục Bài viết'))

@section('content')
<div class="space-y-4">
    <x-admin.page-header
        :title="$isEdit ? __('Sửa thông tin Danh mục') : __('Thêm Danh mục mới')"
        :subtitle="$isEdit
            ? __('Cập nhật nội dung đa ngôn ngữ và cấu hình cho: :name', ['name' => $postCategory->translated_name])
            : __('Tạo danh mục bài viết mới, thiết lập danh mục cha và trạng thái hiển thị')"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Danh mục Bài viết'), 'url' => route('admin.post-categories.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')],
        ]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.post-categories.index') }}" variant="outline-secondary" size="sm">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>{{ __('Quay lại') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <form action="{{ $actionUrl }}" method="POST" id="form-category">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row g-4">
            <div class="col-lg-9 col-md-8">
                <x-admin.card title="{{ __('Nội dung đa ngôn ngữ') }}">
                    @if($languages->isEmpty())
                        <div class="alert alert-warning mb-0 d-flex align-items-center">
                            <svg class="me-2 flex-shrink-0" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span class="small">
                                {{ __('Chưa có ngôn ngữ hoạt động. Vui lòng thêm ngôn ngữ tại') }}
                                <a href="{{ route('admin.languages.index') }}" class="fw-semibold">{{ __('Quản lý Ngôn ngữ') }}</a>
                                {{ __('trước khi tạo danh mục.') }}
                            </span>
                        </div>
                    @else
                        @php
                            $lang = $languages->firstWhere('code', $currentLocale) ?? $languages->first();
                            $trans = ($isEdit && isset($postCategory->translations))
                                ? $postCategory->translations->firstWhere('locale', $lang->code)
                                : null;
                            $nameKey = 'translations.' . $lang->code . '.name';
                            $slugKey = 'translations.' . $lang->code . '.slug';
                            $descKey = 'translations.' . $lang->code . '.description';
                        @endphp
                        <div id="category-lang-panels">
                            <div class="lang-panel" data-lang-panel="{{ $lang->code }}">
                                <x-admin.form-group
                                    label="{{ __('Tên danh mục') }}"
                                    :name="$nameKey"
                                    required
                                    :error="$errors->first($nameKey)"
                                >
                                    <x-admin.input
                                        type="text"
                                        name="translations[{{ $lang->code }}][name]"
                                        value="{{ old('translations.'.$lang->code.'.name', $trans->name ?? '') }}"
                                        class="seo-source-name"
                                        placeholder="{{ __('Nhập tên danh mục...') }}"
                                        required
                                    />
                                </x-admin.form-group>

                                <x-admin.form-group
                                    label="{{ __('Đường dẫn (Slug)') }}"
                                    :name="$slugKey"
                                    description="{{ __('Để trống sẽ tự tạo từ tên.') }}"
                                    :error="$errors->first($slugKey)"
                                >
                                    <x-admin.input
                                        type="text"
                                        name="translations[{{ $lang->code }}][slug]"
                                        value="{{ old('translations.'.$lang->code.'.slug', $trans->slug ?? '') }}"
                                        placeholder="vi-du-danh-muc"
                                        class="seo-source-slug"
                                    />
                                </x-admin.form-group>

                                <x-admin.form-group
                                    label="{{ __('Mô tả') }}"
                                    :name="$descKey"
                                    :error="$errors->first($descKey)"
                                >
                                    <x-admin.textarea
                                        name="translations[{{ $lang->code }}][description]"
                                        rows="4"
                                        placeholder="{{ __('Nhập mô tả danh mục...') }}"
                                    >{{ old('translations.'.$lang->code.'.description', $trans->description ?? '') }}</x-admin.textarea>
                                </x-admin.form-group>

                                <x-admin.seo-meta
                                    :lang="$lang"
                                    :seo="$postCategory?->seoForLocale($lang->code)"
                                />
                            </div>
                        </div>
                    @endif
                </x-admin.card>
            </div>

            <div class="col-lg-3 col-md-4">
                <div class="d-flex flex-column gap-4 post-category-sidebar">
                    <x-admin.language-switcher-widget
                        :languages="$languages"
                        :current-locale="$currentLocale"
                        :translations="$isEdit ? $postCategory->translations : collect()"
                        route-prefix="admin.post-categories"
                        :uuid="$isEdit ? $postCategory->uuid : null"
                        :is-edit="$isEdit"
                    />

                    <x-admin.card title="{{ __('Cấu hình') }}">
                        <x-admin.form-group
                            label="{{ __('Hình đại diện') }}"
                            name="image"
                            description="{{ __('Chọn ảnh từ Thư viện Media. Khuyên dùng ảnh vuông, tối đa 5MB (JPEG, PNG, WEBP).') }}"
                        >
                            <x-admin.image-upload
                                name="image"
                                :current="$isEdit ? ($postCategory->image_url ?? $postCategory->image ?? null) : null"
                                :current-uuid="old('image', $postCategory->image ?? '')"
                                shape="square"
                            />
                        </x-admin.form-group>

                        <hr class="my-4 text-body-tertiary">

                        <x-admin.form-group
                            label="{{ __('Danh mục cha') }}"
                            name="parent_id"
                            description="{{ __('Chọn danh mục cấp trên nếu đây là danh mục con.') }}"
                            :error="$errors->first('parent_id')"
                        >
                            <x-admin.select name="parent_id">
                                <option value="">{{ __('— Không có danh mục cha —') }}</option>
                                @foreach($parents as $parent)
                                    <option value="{{ $parent->id }}" {{ (string) old('parent_id', $postCategory->parent_id ?? '') === (string) $parent->id ? 'selected' : '' }}>
                                        {{ $parent->tree_name ?? $parent->translated_name }}
                                    </option>
                                @endforeach
                            </x-admin.select>
                        </x-admin.form-group>

                        <x-admin.form-group
                            label="{{ __('Thứ tự hiển thị') }}"
                            name="display_order"
                            description="{{ __('Số nhỏ hơn sẽ hiển thị trước.') }}"
                            :error="$errors->first('display_order')"
                        >
                            <x-admin.input
                                type="number"
                                name="display_order"
                                value="{{ old('display_order', $postCategory->display_order ?? 0) }}"
                                min="0"
                            />
                        </x-admin.form-group>

                        <x-admin.toggle
                            name="is_active"
                            label="{{ __('Kích hoạt') }}"
                            description="{{ __('Cho phép danh mục hiển thị ở frontend.') }}"
                            :checked="(bool) old('is_active', $postCategory->is_active ?? true)"
                        />
                    </x-admin.card>

                    <x-admin.card>
                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                            <x-admin.button
                                type="submit"
                                name="submit_action"
                                value="save"
                                variant="primary"
                                :disabled="$languages->isEmpty()"
                            >
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $isEdit ? __('Cập nhật') : __('Lưu') }}</span>
                            </x-admin.button>
                            <x-admin.button
                                type="submit"
                                name="submit_action"
                                value="save_and_edit"
                                variant="outline-secondary"
                                :disabled="$languages->isEmpty()"
                            >
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>{{ __('Lưu & Sửa') }}</span>
                            </x-admin.button>
                        </div>
                    </x-admin.card>
                </div>
            </div>
        </div>
    </form>
</div>

<x-admin.scripts.auto-slug :is-edit="$isEdit" />
<x-admin.scripts.seo-preview />

@pushOnce('styles')
<style>
    @media (min-width: 992px) {
        .post-category-sidebar {
            position: sticky;
            top: 1rem;
        }
    }
</style>
@endPushOnce

@endsection
