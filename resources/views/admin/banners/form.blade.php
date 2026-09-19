@extends('layouts.admin')

@php
    $banner = $banner ?? null;
    $isEdit = !is_null($banner);
@endphp

@section('title', $isEdit ? __('Sửa Banner') : __('Thêm Banner mới'))

@section('content')
    {{-- Page Header --}}
    <x-admin.page-header 
        :title="$isEdit ? __('Sửa Banner') : __('Thêm Banner mới')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Banner & Quảng cáo'), 'url' => route('admin.banners.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')]
        ]" 
    />

    {{-- Main Form --}}
    <form 
        id="banner-form" 
        action="{{ $isEdit ? route('admin.banners.update', $banner->uuid) : route('admin.banners.store') }}" 
        method="POST" 
        enctype="multipart/form-data"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row align-items-start g-4">

            {{-- ======================================================== --}}
            {{-- CỘT TRÁI: TIÊU ĐỀ, HÌNH ẢNH & NỘI DUNG BANNER           --}}
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
                        $translation = $banner?->translate($code);
                        $thumbMedia = $translation?->imageMedia;
                        $currentImgUrl = $translation?->image_url;
                        $currentUuid = old('translations.'.$code.'.image', $thumbMedia?->uuid ?? (preg_match('/^[0-9a-f-]{36}$/i', $translation?->image ?? '') ? $translation?->image : null));
                    @endphp

                    <div class="lang-panel {{ $panelClass }} d-flex flex-column gap-4" data-lang-panel="{{ $code }}">
                        
                        {{-- CARD: THÔNG TIN BANNER THEO NGÔN NGỮ --}}
                        <x-admin.card>
                            {{-- Tiêu đề Banner --}}
                            <x-admin.form-group 
                                :label="__('Tiêu đề banner')" 
                                name="translations.{{ $code }}.title" 
                                :required="$code === $defaultLocale"
                                :description="__('Tên định danh banner hoặc câu khẩu hiệu khuyến mãi')"
                            >
                                <x-admin.input 
                                    type="text" 
                                    name="translations[{{ $code }}][title]" 
                                    size="sm"
                                    value="{{ old('translations.'.$code.'.title', $translation?->title ?? '') }}" 
                                    placeholder="{{ __('Ví dụ: Khuyến mãi mùa hè giảm tới 50%...') }}" 
                                    :required="$code === $defaultLocale"
                                />
                            </x-admin.form-group>

                            {{-- Ảnh Banner --}}
                            <div class="mb-3">
                                <label class="form-label fw-medium">{{ __('Hình ảnh banner') }}</label>
                                <x-admin.image-upload 
                                    name="translations[{{ $code }}][image]" 
                                    :current="$currentImgUrl" 
                                    :current-uuid="$currentUuid" 
                                    shape="wide" 
                                    description="{{ __('Khuyên dùng ảnh tỷ lệ phù hợp với vị trí hiển thị (Slider: 1920x600px, Sidebar: 600x600px). Định dạng: JPG, PNG, WEBP.') }}" 
                                />
                            </div>

                            {{-- Mô tả banner --}}
                            <x-admin.form-group 
                                :label="__('Mô tả / Đoạn giới thiệu')" 
                                name="translations.{{ $code }}.description" 
                                class="mb-0"
                            >
                                <x-admin.textarea 
                                    name="translations[{{ $code }}][description]" 
                                    size="sm" 
                                    rows="3" 
                                    placeholder="{{ __('Nhập mô tả hoặc phụ đề cho banner nếu cần...') }}"
                                >{{ old('translations.'.$code.'.description', $translation?->description ?? '') }}</x-admin.textarea>
                            </x-admin.form-group>
                        </x-admin.card>

                    </div>
                @endforeach

            </div>

            {{-- ======================================================== --}}
            {{-- CỘT PHẢI: CÀI ĐẶT HIỂN THỊ (SIDEBAR)                     --}}
            {{-- ======================================================== --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- HỘP 1: THAO TÁC / LƯU --}}
                <x-admin.card :title="__('Thao tác')">
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" name="submit_action" value="save" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-check-circle me-1"></i> {{ $isEdit ? __('Cập nhật') : __('Lưu banner') }}
                        </button>
                        <button type="submit" name="submit_action" value="save_and_edit" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-pencil-square me-1"></i> {{ __('Lưu & tiếp tục sửa') }}
                        </button>
                        <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                            {{ __('Quay lại') }}
                        </a>
                    </div>
                </x-admin.card>

                {{-- HỘP 2: CẤU HÌNH VỊ TRÍ & LIÊN KẾT --}}
                <x-admin.card :title="__('Cấu hình hiển thị')">
                    {{-- Vị trí --}}
                    <x-admin.form-group :label="__('Vị trí hiển thị')" name="position" required class="mb-3">
                        <select name="position" class="form-select form-select-sm" required>
                            @foreach($positions as $posKey => $posLabel)
                                <option value="{{ $posKey }}" {{ old('position', $banner?->position ?? 'home_slider') === $posKey ? 'selected' : '' }}>
                                    {{ $posLabel }}
                                </option>
                            @endforeach
                        </select>
                    </x-admin.form-group>

                    {{-- Liên kết --}}
                    <x-admin.form-group :label="__('Liên kết đích (URL)')" name="link" class="mb-3">
                        <x-admin.input 
                            type="text" 
                            name="link" 
                            size="sm"
                            value="{{ old('link', $banner?->link ?? '') }}" 
                            placeholder="https://example.com/collection" 
                        />
                    </x-admin.form-group>

                    {{-- Mở tab mới --}}
                    <div class="form-check mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="target" 
                               id="banner_target" 
                               value="_blank" 
                               {{ old('target', $banner?->target ?? '_self') === '_blank' ? 'checked' : '' }}>
                        <label class="form-check-label small" for="banner_target">
                            {{ __('Mở liên kết trong tab mới') }}
                        </label>
                    </div>

                    {{-- Trạng thái kích hoạt --}}
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" 
                               type="checkbox" 
                               name="is_active" 
                               id="is_active" 
                               value="1" 
                               {{ old('is_active', $banner?->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label small fw-medium" for="is_active">
                            {{ __('Kích hoạt hiển thị banner') }}
                        </label>
                    </div>

                    {{-- Thứ tự sắp xếp --}}
                    <x-admin.form-group :label="__('Thứ tự sắp xếp')" name="display_order" class="mb-3">
                        <x-admin.input 
                            type="number" 
                            name="display_order" 
                            size="sm"
                            value="{{ old('display_order', $banner?->display_order ?? 0) }}" 
                            min="0"
                        />
                    </x-admin.form-group>

                    <hr class="my-3 text-body-secondary opacity-25">

                    {{-- Thời gian áp dụng --}}
                    <div class="mb-3">
                        <label class="form-label small fw-medium">{{ __('Ngày bắt đầu hiển thị') }}</label>
                        <input type="datetime-local" 
                               name="start_date" 
                               class="form-control form-control-sm" 
                               value="{{ old('start_date', $banner?->start_date ? $banner->start_date->format('Y-m-d\TH:i') : '') }}">
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-medium">{{ __('Ngày kết thúc') }}</label>
                        <input type="datetime-local" 
                               name="end_date" 
                               class="form-control form-control-sm" 
                               value="{{ old('end_date', $banner?->end_date ? $banner->end_date->format('Y-m-d\TH:i') : '') }}">
                        <div class="form-text small">{{ __('Để trống nếu banner hiển thị vô thời hạn.') }}</div>
                    </div>
                </x-admin.card>

            </div>
        </div>
    </form>
@endsection