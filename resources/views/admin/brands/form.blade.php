@extends('layouts.admin')

@php
    $brand = $brand ?? null;
    $isEdit = !is_null($brand);
@endphp

@section('title', $isEdit ? __('Sửa Thương hiệu') : __('Thêm Thương hiệu mới'))

@section('content')
    {{-- Page Header --}}
    <x-admin.page-header 
        :title="$isEdit ? __('Sửa Thương hiệu') : __('Thêm Thương hiệu mới')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Thương hiệu'), 'url' => route('admin.brands.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')]
        ]" 
    />

    {{-- Main Form --}}
    <form 
        id="brand-form" 
        action="{{ $isEdit ? route('admin.brands.update', $brand->uuid) : route('admin.brands.store') }}" 
        method="POST" 
        enctype="multipart/form-data"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row align-items-start g-4">

            {{-- ======================================================== --}}
            {{-- CỘT TRÁI: NỘI DUNG ĐA NGÔN NGỮ & SEO                    --}}
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
                        $translation = $brand?->translate($code);
                    @endphp

                    <div class="lang-panel {{ $panelClass }} d-flex flex-column gap-4" data-lang-panel="{{ $code }}">
                        
                        {{-- CARD 1: THÔNG TIN THƯƠNG HIỆU --}}
                        <x-admin.card>
                            {{-- Tên thương hiệu --}}
                            <x-admin.form-group 
                                :label="__('Tên thương hiệu')" 
                                name="translations.{{ $code }}.name" 
                                :required="$code === $defaultLocale"
                            >
                                <x-admin.input 
                                    type="text" 
                                    name="translations[{{ $code }}][name]" 
                                    size="sm"
                                    value="{{ old('translations.'.$code.'.name', $translation?->name ?? '') }}" 
                                    placeholder="{{ __('Ví dụ: Apple, Samsung, Nike...') }}" 
                                    class="seo-source-name"
                                    :required="$code === $defaultLocale"
                                />
                            </x-admin.form-group>

                            {{-- Slug --}}
                            <x-admin.form-group 
                                :label="__('Liên kết tĩnh (Slug)')" 
                                name="translations.{{ $code }}.slug"
                                :description="__('Để trống hệ thống sẽ tự động tạo từ tên thương hiệu.')"
                            >
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-body-secondary text-body-secondary">{{ url('/brands') }}/</span>
                                    <x-admin.input 
                                        type="text" 
                                        name="translations[{{ $code }}][slug]" 
                                        size="sm" 
                                        value="{{ old('translations.'.$code.'.slug', $translation?->slug ?? '') }}" 
                                        placeholder="thuong-hieu" 
                                        class="seo-source-slug" 
                                    />
                                </div>
                            </x-admin.form-group>

                            {{-- Mô tả thương hiệu --}}
                            <x-admin.form-group 
                                :label="__('Mô tả thương hiệu')"
                                name="translations.{{ $code }}.description" 
                                :description="__('Thông tin giới thiệu tóm lược về lịch sử hoặc sản phẩm của thương hiệu.')"
                                class="mb-0"
                            >
                                <x-admin.textarea 
                                    name="translations[{{ $code }}][description]" 
                                    size="sm" 
                                    rows="4" 
                                    class="seo-source-excerpt"
                                    placeholder="{{ __('Nhập mô tả về thương hiệu...') }}"
                                >{{ old('translations.'.$code.'.description', $translation?->description ?? '') }}</x-admin.textarea>
                            </x-admin.form-group>
                        </x-admin.card>

                        {{-- CARD 2: TỐI ƯU SEO --}}
                        <x-admin.card :title="__('Tối ưu hóa Công cụ Tìm kiếm (SEO)')">
                            <x-admin.seo-meta 
                                :lang="$lang" 
                                :seo="$brand?->seoForLocale($code)" 
                                :show-header="false"
                            />
                        </x-admin.card>

                    </div>
                @endforeach

            </div>

            {{-- ======================================================== --}}
            {{-- CỘT PHẢI: CÀI ĐẶT THƯƠNG HIỆU (SIDEBAR)                  --}}
            {{-- ======================================================== --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- HỘP 1: THAO TÁC / LƯU --}}
                <x-admin.card :title="__('Thao tác')">
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" name="submit_action" value="save" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-check-circle me-1"></i> {{ $isEdit ? __('Cập nhật') : __('Lưu thương hiệu') }}
                        </button>
                        <button type="submit" name="submit_action" value="save_and_edit" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-pencil-square me-1"></i> {{ __('Lưu & tiếp tục sửa') }}
                        </button>
                        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                            {{ __('Quay lại') }}
                        </a>
                    </div>
                </x-admin.card>

                {{-- HỘP 2: LOGO THƯƠNG HIỆU --}}
                <x-admin.card :title="__('Logo thương hiệu')">
                    @php 
                        $thumbMedia = $brand ? ($brand->logoMedia ?? null) : null; 
                        $currentImgUrl = $brand?->logo_url ?? ($brand?->logo && !preg_match('/^[0-9a-f-]{36}$/i', $brand->logo) ? asset('storage/' . $brand->logo) : null);
                        $currentUuid = old('logo_uuid', $thumbMedia?->uuid ?? ($brand?->logo && preg_match('/^[0-9a-f-]{36}$/i', $brand->logo) ? $brand->logo : null));
                    @endphp
                    <div class="mb-0">
                        <x-admin.image-upload 
                            name="logo" 
                            :current="$currentImgUrl" 
                            :current-uuid="$currentUuid" 
                            shape="square" 
                            description="{{ __('Khuyên dùng ảnh vuông 400x400px. Định dạng: PNG, WEBP, SVG.') }}" 
                        />
                    </div>
                </x-admin.card>

                {{-- HỘP 3: THÔNG TIN BỔ SUNG --}}
                <x-admin.card :title="__('Thông tin bổ sung')">
                    {{-- Trạng thái hiển thị --}}
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input"
                               type="checkbox"
                               name="is_active"
                               id="is_active"
                               value="1"
                               {{ old('is_active', $brand?->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-medium" for="is_active">
                            {{ __('Kích hoạt hiển thị thương hiệu') }}
                        </label>
                    </div>

                    <x-admin.form-group :label="__('Website chính thức')" name="website" class="mb-3">
                        <x-admin.input
                            type="url"
                            name="website"
                            size="sm"
                            value="{{ old('website', $brand?->website ?? '') }}"
                            placeholder="https://example.com"
                        />
                    </x-admin.form-group>

                    <x-admin.form-group :label="__('Thứ tự sắp xếp')" name="display_order" class="mb-0">
                        <x-admin.input
                            type="number"
                            name="display_order"
                            size="sm"
                            value="{{ old('display_order', $brand?->display_order ?? 0) }}"
                            min="0"
                        />
                    </x-admin.form-group>
                </x-admin.card>

            </div>
        </div>
    </form>

    <x-admin.scripts.auto-slug :is-edit="$isEdit" />
@endsection