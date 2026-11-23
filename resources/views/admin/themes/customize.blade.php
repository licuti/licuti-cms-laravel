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
                @forelse($schema['sections'] as $section)
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
                @empty
                    <x-admin.card>
                        <div class="text-center py-4 text-body-secondary">Theme này chưa khai báo cấu hình tùy biến.</div>
                    </x-admin.card>
                @endforelse
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
