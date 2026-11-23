@extends('layouts.admin')

@section('title', 'Tùy biến Theme')

@section('content')
    <x-admin.page-header
        title="Tùy biến {{ $theme['name'] ?? $theme['slug'] }}"
        subtitle="Cấu hình riêng của theme được lưu độc lập và không mất khi đổi theme."
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => route('admin.dashboard')],
            ['label' => 'Theme', 'url' => route('admin.themes.index')],
            ['label' => 'Tùy biến']
        ]"
    >
        <x-slot:actions>
            @can('themes.preview')
                <form action="{{ route('admin.themes.preview', $theme['slug']) }}" method="POST" class="d-inline">
                    @csrf
                    <x-admin.button type="submit" variant="outline-secondary">Xem trước</x-admin.button>
                </form>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row g-4 align-items-start">
        <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">
            <form id="theme-settings-form" action="{{ route('admin.themes.update', $theme['slug']) }}" method="POST">
                @csrf
                @method('PUT')
                @if(!empty($schema['sections']))
                    <div class="row g-4 align-items-start theme-customize-layout">
                        {{-- Cột 4: danh sách group cấu hình --}}
                        <div class="col-12 col-lg-4">
                            <div class="theme-section-tabs nav nav-pills flex-column gap-2" role="tablist" aria-label="Danh mục cấu hình theme">
                                @foreach($schema['sections'] as $section)
                                    @php($sectionId = 'theme-section-' . \Illuminate\Support\Str::slug($section['key']))
                                    <button
                                        type="button"
                                        class="theme-section-tab nav-link text-start {{ $loop->first ? 'active' : '' }}"
                                        id="{{ $sectionId }}-tab"
                                        data-bs-toggle="pill"
                                        data-bs-target="#{{ $sectionId }}"
                                        role="tab"
                                        aria-controls="{{ $sectionId }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                    >
                                        <span class="theme-section-tab-label">{{ $section['label'] ?? $section['key'] }}</span>
                                        @if($section['description'] ?? null)
                                            <span class="theme-section-tab-description">{{ $section['description'] }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Cột 8: các field option của group đang chọn --}}
                        <div class="col-12 col-lg-8">
                            <div class="tab-content theme-section-content">
                                @foreach($schema['sections'] as $section)
                                    @php($sectionId = 'theme-section-' . \Illuminate\Support\Str::slug($section['key']))
                                    <div
                                        class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                        id="{{ $sectionId }}"
                                        role="tabpanel"
                                        aria-labelledby="{{ $sectionId }}-tab"
                                        tabindex="0"
                                    >
                                        <x-admin.card title="{{ $section['label'] ?? $section['key'] }}">
                                            @if($section['description'] ?? null)
                                                <p class="text-body-secondary small">{{ $section['description'] }}</p>
                                            @endif
                                            <div class="d-flex flex-column gap-3">
                                                @foreach($section['fields'] ?? [] as $field)
                                                    @php($key = $field['key'])
                                                    @php($value = old('settings.'.$key, $values[$key] ?? ($field['default'] ?? null)))
                                                    <x-admin.form-group
                                                        label="{{ $field['label'] ?? $key }}"
                                                        name="settings.{{ $key }}"
                                                        :description="$field['description'] ?? null"
                                                    >
                                                        @if($field['type'] === 'textarea')
                                                            <x-admin.textarea name="settings[{{ $key }}]" rows="4">{{ $value }}</x-admin.textarea>
                                                        @elseif($field['type'] === 'color')
                                                            <x-admin.input type="color" name="settings[{{ $key }}]" value="{{ $value ?: '#000000' }}" style="width: 4rem; height: 2.25rem; padding: .15rem;" />
                                                        @elseif($field['type'] === 'number')
                                                            <x-admin.input type="number" name="settings[{{ $key }}]" value="{{ $value }}" :min="$field['min'] ?? null" :max="$field['max'] ?? null" />
                                                        @elseif($field['type'] === 'boolean')
                                                            <x-admin.toggle name="settings[{{ $key }}]" :checked="(bool) $value" label="Bật tùy chọn này" />
                                                        @elseif($field['type'] === 'select')
                                                            <x-admin.select name="settings[{{ $key }}]">
                                                                @foreach($field['options'] ?? [] as $option => $label)
                                                                    <option value="{{ $option }}" @selected($value === $option)>{{ $label }}</option>
                                                                @endforeach
                                                            </x-admin.select>
                                                        @else
                                                            <x-admin.input name="settings[{{ $key }}]" value="{{ $value }}" />
                                                        @endif
                                                    </x-admin.form-group>
                                                @endforeach
                                            </div>
                                        </x-admin.card>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <x-admin.card>
                        <div class="text-center py-4 text-body-secondary">Theme này chưa khai báo cấu hình tùy biến.</div>
                    </x-admin.card>
                @endif
            </form>
        </div>

        <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">
                <x-admin.card title="Trạng thái">
                    <div class="d-flex flex-column gap-3">
                        <div class="small text-body-secondary">Slug</div>
                        <code>{{ $theme['slug'] }}</code>
                        <div class="small text-body-secondary">Phiên bản</div>
                        <span>{{ $theme['version'] ?? '---' }}</span>
                        @can('themes.activate')
                            <form action="{{ route('admin.themes.activate', $theme['slug']) }}" method="POST">
                                @csrf
                                <x-admin.button type="submit" variant="primary" class="w-100">Kích hoạt theme</x-admin.button>
                            </form>
                        @endcan
                    </div>
                </x-admin.card>
                <x-admin.card>
                    <x-admin.button href="{{ route('admin.themes.index') }}" variant="outline-secondary">Quay lại danh sách</x-admin.button>
                    @can('themes.update')
                        <x-admin.button type="submit" form="theme-settings-form" variant="primary">Lưu cấu hình</x-admin.button>
                    @endcan
                </x-admin.card>
            </div>
        </div>
    </div>
@endsection
