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

@section('title', $isEdit ? __('Sửa Danh mục: :name', ['name' => $postCategory->translated_name]) : __('Thêm Danh mục mới'))

@section('content')
<div class="space-y-6">
    <x-admin.page-header
        :title="$isEdit ? __('Sửa thông tin Danh mục') : __('Thêm Danh mục mới')"
        :subtitle="$isEdit 
            ? __('Cập nhật nội dung đa ngôn ngữ và cấu hình cho: :name', ['name' => $postCategory->translated_name])
            : __('Tạo danh mục sản phẩm mới, thiết lập danh mục cha và trạng thái hiển thị')"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Danh mục Bài viết'), 'url' => route('admin.post-categories.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')],
        ]"
    />

    <form action="{{ $actionUrl }}" method="POST" class="space-y-6" id="form-category">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            <div class="md:col-span-8 lg:col-span-9 space-y-6">
                <x-admin.card title="{{ __('Nội dung đa ngôn ngữ') }}" class="p-6 sm:p-8">
                    <div class="space-y-6">
                        @if($languages->isEmpty())
                            <div class="rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-400">
                                {{ __('Chưa có ngôn ngữ hoạt động. Vui lòng thêm ngôn ngữ tại') }}
                                <a href="{{ route('admin.languages.index') }}" class="font-semibold underline hover:text-amber-600">{{ __('Quản lý Ngôn ngữ') }}</a>
                                {{ __('trước khi tạo danh mục.') }}
                            </div>
                        @else
                            <div id="category-lang-panels">
                                @php
                                    $lang = $languages->firstWhere('code', $currentLocale) ?? $languages->first();
                                    $trans = ($isEdit && isset($postCategory->translations))
                                        ? $postCategory->translations->firstWhere('locale', $lang->code)
                                        : null;
                                    $nameKey = 'translations.' . $lang->code . '.name';
                                    $slugKey = 'translations.' . $lang->code . '.slug';
                                    $descKey = 'translations.' . $lang->code . '.description';
                                @endphp
                                <div class="lang-panel space-y-6" data-lang-panel="{{ $lang->code }}">
                                    <x-admin.form-group
                                        label="{{ __('Tên danh mục') }}"
                                        :name="$nameKey"
                                        required
                                        :error="$errors->first($nameKey)"
                                    >
                                        <x-admin.input type="text" name="translations[{{ $lang->code }}][name]" value="{{ old('translations.'.$lang->code.'.name', $trans->name ?? '') }}" class="seo-source-name"
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
                                        <x-admin.input type="text" name="translations[{{ $lang->code }}][slug]" value="{{ old('translations.'.$lang->code.'.slug', $trans->slug ?? '') }}"
                                            placeholder="vi-du-danh-muc" class="seo-source-slug" />
                                    </x-admin.form-group>

                                    <x-admin.form-group
                                        label="{{ __('Mô tả') }}"
                                        :name="$descKey"
                                        :error="$errors->first($descKey)"
                                    >
                                        <x-admin.textarea name="translations[{{ $lang->code }}][description]" rows="4" placeholder="{{ __('Nhập mô tả danh mục...') }}">{{ old('translations.'.$lang->code.'.description', $trans->description ?? '') }}</x-admin.textarea>
                                    </x-admin.form-group>
                                    <x-admin.seo-meta 
                                        :lang="$lang" 
                                        :seo="$postCategory?->seoForLocale($code)" 
                                    />
                                </div>
                            </div>
                        @endif
                    </div>
                </x-admin.card>
            </div>

            <div class="md:col-span-4 lg:col-span-3 space-y-6">
                <x-admin.language-switcher-widget 
                    :languages="$languages" 
                    :current-locale="$currentLocale" 
                    :translations="$isEdit ? $postCategory->translations : collect()" 
                    route-prefix="admin.post-categories" 
                    :uuid="$isEdit ? $postCategory->uuid : null" 
                    :is-edit="$isEdit" 
                />

                <x-admin.card title="{{ __('Cấu hình') }}" class="p-6">
                    <div class="space-y-6">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">{{ __('Hình đại diện') }}</p>
                            <x-admin.image-upload
                                name="image"
                                :current="$isEdit ? ($postCategory->image_url ?? $postCategory->image ?? null) : null"
                                :current-uuid="old('image', $postCategory->image ?? '')"
                                shape="square"
                                description="{{ __('Chọn ảnh từ Thư viện Media') }}"
                            />
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-2 leading-relaxed">
                                {{ __('Khuyên dùng ảnh vuông, tối đa 5MB (JPEG, PNG, WEBP).') }}
                            </p>
                        </div>

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

                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <x-admin.toggle
                                name="is_active"
                                label="{{ __('Kích hoạt') }}"
                                
                                :checked="(bool) old('is_active', $postCategory->is_active ?? true)"
                            />
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-end gap-1">
                            <x-admin.button href="{{ route('admin.post-categories.index') }}" variant="secondary">
                                <span>{{ __('Quay lại') }}</span>
                            </x-admin.button>
                            <x-admin.button type="submit" name="submit_action" value="save" variant="primary" :disabled="$languages->isEmpty()">
                                <span>{{ __('Lưu') }}</span>
                            </x-admin.button>
                            <x-admin.button type="submit" name="submit_action" value="save_and_edit" variant="outline" :disabled="$languages->isEmpty()">
                                <span>{{ __('Lưu & Sửa') }}</span>
                            </x-admin.button>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </form>
</div>




<x-admin.scripts.auto-slug :is-edit="$isEdit" />
<x-admin.scripts.seo-preview />

@endsection
