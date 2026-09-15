@extends('layouts.admin')

@php
    $isEdit = isset($page);
    $locale = app()->getLocale();
    $currentTrans = $isEdit
        ? ($page->translations->firstWhere('locale', $locale) ?? $page->translations->first())
        : null;
    $displayName = $currentTrans?->title ?? '';
    $title = $isEdit ? (__('Sửa trang tĩnh') . ($displayName ? ': ' . $displayName : '')) : __('Thêm Trang tĩnh mới');
    $pageTitle = $isEdit ? __('Sửa thông tin Trang tĩnh') : __('Thêm Trang tĩnh mới');
    $subtitle = $isEdit
        ? (__('Cập nhật nội dung đa ngôn ngữ cho: ') . ($displayName ?: __('trang tĩnh')))
        : __('Tạo trang tĩnh mới, thiết lập trạng thái hiển thị');
    $actionUrl = $isEdit
        ? route('admin.pages.update', $page->uuid)
        : route('admin.pages.store');
    $activeLanguages = $languages ?? \App\Models\Language::query()->where('is_active', true)->orderBy('display_order')->get();
    $defaultLocale = optional($activeLanguages->firstWhere('is_default', true))->code
        ?? optional($activeLanguages->first())->code
        ?? config('app.locale', 'vi');
@endphp

@section('title', $title)

@section('content')
<div class="space-y-6">
    <x-admin.page-header
        :title="$pageTitle"
        :subtitle="$subtitle"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Trang tĩnh'), 'url' => route('admin.pages.index')],
            ['label' => $isEdit ? (__('Sửa') . ($displayName ? ': ' . $displayName : '')) : __('Thêm mới')],
        ]"
    />

    <form action="{{ $actionUrl }}" method="POST" class="space-y-6" id="form-page">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            <div class="md:col-span-8 lg:col-span-9 space-y-6">
                <x-admin.card title="{{ __('Nội dung đa ngôn ngữ') }}" class="p-6 sm:p-8">
                    <div class="space-y-6">
                        @if($activeLanguages->isEmpty())
                            <div class="rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-400">
                                {{ __('Chưa có ngôn ngữ hoạt động. Vui lòng thêm ngôn ngữ tại') }}
                                <a href="{{ route('admin.languages.index') }}" class="font-semibold underline hover:text-amber-600">{{ __('Quản lý Ngôn ngữ') }}</a>
                                {{ __('trước khi tạo trang tĩnh.') }}
                            </div>
                        @else
                            <div class="flex flex-wrap items-center gap-1 border-b border-slate-200 dark:border-slate-800" role="tablist" id="pages-lang-tabs">
                                @foreach($activeLanguages as $lang)
                                    <button
                                        type="button"
                                        role="tab"
                                        data-lang-tab="{{ $lang->code }}"
                                        aria-selected="{{ $lang->code === $defaultLocale ? 'true' : 'false' }}"
                                        class="lang-tab-btn inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors
                                            {{ $lang->code === $defaultLocale
                                                ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                                : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-600' }}"
                                    >
                                        @if($lang->flag_url)
                                            <img src="{{ $lang->flag_url }}" alt="" class="w-5 h-auto rounded-sm border border-slate-200 dark:border-slate-700">
                                        @endif
                                        <span>{{ $lang->native_name ?: $lang->name }}</span>
                                        <span class="text-[10px] uppercase font-bold text-slate-400">{{ $lang->code }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <div id="pages-lang-panels">
                                @foreach($activeLanguages as $lang)
                                    @php
                                        $trans = $isEdit
                                            ? $page->translations->firstWhere('locale', $lang->code)
                                            : null;
                                        $isDefaultPanel = $lang->code === $defaultLocale;
                                        $titleKey = 'translations.' . $lang->code . '.title';
                                        $contentKey = 'translations.' . $lang->code . '.content';
                                    @endphp
                                    <div class="lang-panel space-y-6 {{ $isDefaultPanel ? '' : 'hidden' }}"
                                         data-lang-panel="{{ $lang->code }}"
                                         role="tabpanel"
                                    >
                                        <x-admin.form-group
                                            label="{{ __('Tiêu đề trang') }}"
                                            :name="$titleKey"
                                            description="{{ __('Tiêu đề hiển thị trên website.') }}"
                                            required
                                        >
                                            <x-admin.input
                                                type="text"
                                                name="translations[{{ $lang->code }}][title]"
                                                value="{{ old('translations.'.$lang->code.'.title', $trans?->title) }}"
                                                placeholder="Nhập tiêu đề trang..."
                                                required
                                            />
                                        </x-admin.form-group>

                                        <x-admin.form-group
                                            label="{{ __('Nội dung') }}"
                                            :name="$contentKey"
                                            description="{{ __('Nội dung trang tĩnh (HTML).') }}"
                                        >
                                            <textarea
                                                name="translations[{{ $lang->code }}][content]"
                                                rows="12"
                                                placeholder="Nhập nội dung trang tĩnh..."
                                                class="w-full px-3.5 py-2.5 text-sm rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 transition-all"
                                            >{{ old('translations.'.$lang->code.'.content', $trans?->content ?? '') }}</textarea>
                                        </x-admin.form-group>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </x-admin.card>
            </div>

            <div class="md:col-span-4 lg:col-span-3 space-y-6">
                <x-admin.card title="{{ __('Cấu hình') }}" class="p-6">
                    <div class="space-y-6">
                        <x-admin.form-group
                            label="{{ __('Thứ tự hiển thị') }}"
                            name="display_order"
                        >
                            <x-admin.input
                                type="number"
                                name="display_order"
                                value="{{ old('display_order', $isEdit ? $page->display_order : 0) }}"
                                min="0"
                            />
                        </x-admin.form-group>

                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <x-admin.toggle
                                name="is_active"
                                label="{{ __('Kích hoạt') }}"
                                description="{{ __('Bật để hiển thị trang tĩnh trên website.') }}"
                                :checked="(bool) old('is_active', $isEdit ? $page->is_active : true)"
                            />
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-3">{{ __('Cấu hình SEO') }}</h4>

                            <x-admin.form-group label="Meta Title" name="meta_title" description="{{ __('Tiêu đề meta cho trang.') }}">
                                <x-admin.input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title ?? '') }}" />
                            </x-admin.form-group>

                            <x-admin.form-group label="Meta Description" name="meta_description" description="{{ __('Mô tả meta cho trang.') }}">
                                <x-admin.input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description ?? '') }}" />
                            </x-admin.form-group>

                            <x-admin.form-group label="Meta Keywords" name="meta_keywords" description="{{ __('Từ khóa meta.') }}">
                                <x-admin.input type="text" name="meta_keywords" value="{{ old('meta_keywords', $page->meta_keywords ?? '') }}" />
                            </x-admin.form-group>
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-end gap-1">
                            <x-admin.button href="{{ route('admin.pages.index') }}" variant="secondary">
                                <span>{{ __('Quay lại') }}</span>
                            </x-admin.button>
                            <x-admin.button type="submit" name="submit_action" value="save" variant="primary">
                                <span>{{ __('Lưu') }}</span>
                            </x-admin.button>
                            <x-admin.button type="submit" name="submit_action" value="save_and_edit" variant="outline">
                                <span>{{ __('Lưu & Sửa') }}</span>
                            </x-admin.button>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabButtons = document.querySelectorAll('.lang-tab-btn');
        const panels = document.querySelectorAll('.lang-panel');

        const activeClasses = ['border-blue-600', 'text-blue-600', 'dark:border-blue-400', 'dark:text-blue-400'];
        const inactiveClasses = ['border-transparent', 'text-slate-500', 'dark:text-slate-400'];

        function activateLang(code) {
            tabButtons.forEach(function (btn) {
                const isActive = btn.getAttribute('data-lang-tab') === code;
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                activeClasses.forEach(function (c) { btn.classList.toggle(c, isActive); });
                inactiveClasses.forEach(function (c) { btn.classList.toggle(c, !isActive); });
            });
            panels.forEach(function (panel) {
                panel.classList.toggle('hidden', panel.getAttribute('data-lang-panel') !== code);
            });
        }

        tabButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                activateLang(btn.getAttribute('data-lang-tab'));
            });
        });
    });
</script>
@endpush
