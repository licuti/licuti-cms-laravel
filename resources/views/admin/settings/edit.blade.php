@extends('layouts.admin')
@section('title', 'Cấu hình hệ thống')

@php
    $groupLabels = [
        'general' => ['icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'label' => 'Cơ bản'],
        'seo' => ['icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 'label' => 'SEO'],
        'mail' => ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label' => 'Email SMTP'],
        'social' => ['icon' => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z', 'label' => 'Mạng xã hội'],
        'appearance' => ['icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01', 'label' => 'Giao diện'],
    ];
    $hasTranslatable = $settings->where('is_translatable', true)->count() > 0;
    $defaultLocale = app()->getLocale();
@endphp

@section('content')
<div class="mb-4">
    <x-admin.page-header 
        title="Cấu hình hệ thống" 
        subtitle="Quản lý các thông số chung, SEO, giao diện và kết nối của nền tảng"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => route('admin.dashboard')], 
            ['label' => 'Cài đặt hệ thống']
        ]"
    >
        <x-slot:actions>
            <x-admin.button type="button" variant="primary" onclick="document.getElementById('settings-form').submit();">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Lưu Cấu hình
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>
</div>

<div class="row g-4 align-items-start">
    <!-- Cột trái: Tab Navigation -->
    <div class="col-12 col-xl-3 col-lg-4">
        <div class="list-group shadow-sm">
            @foreach($groups as $g)
                <a href="{{ route('admin.settings.edit', $g) }}" 
                   class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 {{ $group === $g ? 'active' : '' }}">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="{{ $group === $g ? 'text-white' : 'text-body-secondary' }}">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $groupLabels[$g]['icon'] }}"></path>
                    </svg>
                    <span class="fw-medium">{{ $groupLabels[$g]['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Cột phải: Form Content -->
    <div class="col-12 col-xl-9 col-lg-8">
        <form id="settings-form" action="{{ route('admin.settings.update', $group) }}" method="POST">
            @csrf
            
            <x-admin.card title="{!! $groupLabels[$group]['label'] !!}">
                @if($hasTranslatable)
                    <div class="mb-4">
                        <x-admin.lang-tabs :active-languages="$activeLanguages" :default-locale="$defaultLocale" />
                    </div>
                @endif
                
                <div class="d-flex flex-column gap-4">
                    @forelse($settings as $setting)
                        @php
                            $value = $setting->value;
                            if ($setting->is_translatable) {
                                $decoded = json_decode($value, true);
                                $value = is_array($decoded) ? $decoded : [$defaultLocale => $value];
                            }
                        @endphp

                        <div class="row g-3">
                            <div class="col-12 col-lg-4">
                                <label class="form-label fw-bold mb-1">
                                    {{ $setting->label ?: $setting->key }}
                                </label>
                                @if($setting->is_translatable)
                                    <div class="mb-2">
                                        <x-admin.badge label="Đa ngôn ngữ" color="primary" class="fw-medium" />
                                    </div>
                                @endif
                                @if($setting->description)
                                    <div class="form-text mt-0">{{ $setting->description }}</div>
                                @endif
                            </div>
                            
                            <div class="col-12 col-lg-8">
                                @if($setting->is_translatable)
                                    @foreach($activeLanguages as $lang)
                                        <div class="lang-panel {{ $lang->code !== $defaultLocale ? 'd-none' : '' }}" data-lang-panel="{{ $lang->code }}">
                                            @if($setting->type === 'textarea')
                                                <x-admin.textarea name="{{ $setting->key }}[{{ $lang->code }}]" rows="3" class="form-control-sm">{{ $value[$lang->code] ?? '' }}</x-admin.textarea>
                                            @else
                                                <x-admin.input type="{{ $setting->type === 'color' ? 'color' : 'text' }}" name="{{ $setting->key }}[{{ $lang->code }}]" value="{{ $value[$lang->code] ?? '' }}" size="sm" />
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    @if($setting->type === 'textarea')
                                        <x-admin.textarea name="{{ $setting->key }}" rows="3" class="form-control-sm">{{ $value }}</x-admin.textarea>
                                    @elseif($setting->type === 'image')
                                        @php
                                            $mediaUuid = $value ? \App\Models\Media::where('file_path', $value)->value('uuid') : null;
                                        @endphp
                                        <div style="max-width: 320px;">
                                            <x-admin.image-upload 
                                                name="{{ $setting->key }}" 
                                                :current="$value ? Storage::url($value) : ''" 
                                                :current-uuid="$mediaUuid"
                                                shape="wide"
                                                description="Chọn ảnh từ Thư viện Media"
                                            />
                                        </div>
                                    @elseif($setting->type === 'color')
                                        <div class="d-flex align-items-center gap-3">
                                            <x-admin.input type="color" name="{{ $setting->key }}" value="{{ $value ?: '#000000' }}" title="Chọn màu" size="sm" style="width: 3.5rem; height: 2rem; padding: 0.15rem;" />
                                            <div class="font-monospace small text-body-secondary">{{ $value ?: '#000000' }}</div>
                                        </div>
                                    @else
                                        <x-admin.input type="{{ $setting->type === 'number' ? 'number' : 'text' }}" name="{{ $setting->key }}" value="{{ $value }}" size="sm" />
                                    @endif
                                @endif
                            </div>
                        </div>

                        @if(!$loop->last)
                            <hr class="my-0 text-body-tertiary">
                        @endif
                    @empty
                        <div class="text-center py-5 text-body-secondary">
                            Chưa có cài đặt nào trong nhóm này.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 pt-4 border-top d-flex justify-content-end">
                    <x-admin.button type="submit" variant="primary">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Lưu Cấu hình
                    </x-admin.button>
                </div>
            </x-admin.card>
        </form>
    </div>
</div>
@endsection
