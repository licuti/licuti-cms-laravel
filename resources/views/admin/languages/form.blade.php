@extends('layouts.admin')

@php
    $isEdit = isset($language);
    $actionUrl = $isEdit ? route('admin.languages.update', $language->code) : route('admin.languages.store');
    $title = $isEdit ? 'Cập nhật Ngôn ngữ' : 'Thêm Ngôn ngữ';
    $subtitle = $isEdit ? 'Chỉnh sửa thông tin ngôn ngữ' : 'Khởi tạo ngôn ngữ mới cho hệ thống';
@endphp

@section('title', $title)

@section('content')
<div class="space-y-6">
    <x-admin.page-header 
        :title="$title" 
        :subtitle="$subtitle"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => route('admin.dashboard')], 
            ['label' => 'Ngôn ngữ', 'url' => route('admin.languages.index')],
            ['label' => $isEdit ? 'Cập nhật' : 'Thêm mới']
        ]"
    />

    <form action="{{ $actionUrl }}" method="POST" class="space-y-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            <!-- Cột Trái (9 phần): Thông tin Ngôn ngữ -->
            <div class="md:col-span-8 lg:col-span-9 space-y-6">
                <x-admin.card title="Thông tin Ngôn ngữ" class="p-6 sm:p-8">
                    <div class="space-y-6">
                        @if(!$isEdit && !empty($isoLanguages))
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700 mb-6">
                                <x-admin.form-group label="Chọn nhanh từ chuẩn ISO" name="iso_selector" description="Tự động điền mã, tên và cờ quốc gia. Bạn vẫn có thể chỉnh sửa lại sau khi chọn.">
                                    <x-admin.select id="iso_language_selector" name="iso_selector">
                                        <option value="">-- Tự nhập thủ công --</option>
                                        @foreach($isoLanguages as $code => $data)
                                            <option value="{{ $code }}" data-info="{{ json_encode($data) }}">
                                                {{ $data['flag'] }} {{ $data['name'] }} ({{ $data['native_name'] }})
                                            </option>
                                        @endforeach
                                    </x-admin.select>
                                </x-admin.form-group>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-admin.form-group label="Mã ngôn ngữ (Code)" name="code" required>
                                <x-admin.input type="text" name="code" value="{{ old('code', $language->code ?? '') }}" required placeholder="VD: vi, en, ja..." />
                            </x-admin.form-group>

                            <x-admin.form-group label="Thứ tự hiển thị" name="display_order" description="Thứ tự xuất hiện trên web (tăng dần).">
                                <x-admin.input type="number" name="display_order" value="{{ old('display_order', $language->display_order ?? 0) }}" min="0" />
                            </x-admin.form-group>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-admin.form-group label="Tên hiển thị" name="name" required>
                                <x-admin.input type="text" name="name" value="{{ old('name', $language->name ?? '') }}" required placeholder="VD: Vietnamese" />
                            </x-admin.form-group>

                            <x-admin.form-group label="Tên bản địa" name="native_name" required>
                                <x-admin.input type="text" name="native_name" value="{{ old('native_name', $language->native_name ?? '') }}" required placeholder="VD: Tiếng Việt" />
                            </x-admin.form-group>
                        </div>

                        <x-admin.form-group label="Cờ quốc gia (Flag)" name="flag_media_uuid" description="Có thể chọn ảnh cờ từ thư viện Media. Nếu để trống sẽ sử dụng tự động (từ CDN).">
                            <div class="max-w-[200px]">
                                <x-admin.image-upload 
                                    name="flag" 
                                    :current="(isset($language) && $language->flag) ? $language->flag_url : ''" 
                                    shape="wide"
                                />
                            </div>
                        </x-admin.form-group>
                    </div>
                </x-admin.card>
            </div>

            <!-- Cột Phải (3 phần): Trạng thái -->
            <div class="md:col-span-4 lg:col-span-3 space-y-6">
                <x-admin.card title="Cấu hình & Trạng thái" class="p-6">
                    <div class="space-y-6">
                        @php
                            $isDefault = $isEdit && $language->is_default;
                        @endphp
                        
                        <div class="space-y-6">
                            <!-- Ngôn ngữ Mặc định -->
                            <x-admin.toggle 
                                name="is_default" 
                                label="Ngôn ngữ mặc định" 
                                :description="$isDefault ? 'Đang là mặc định. Hãy set ngôn ngữ khác làm mặc định để gỡ bỏ.' : ''"
                                :checked="old('is_default', $language->is_default ?? false)"
                                :readonly="$isDefault"
                            />

                            <!-- Trạng thái Hoạt động -->
                            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                                <x-admin.toggle 
                                    name="is_active" 
                                    label="Trạng thái (Active)" 
                                    description="Bật để hiển thị ngôn ngữ này trên website."
                                    :checked="old('is_active', $language->is_active ?? true)"
                                    :readonly="$isDefault"
                                />
                            </div>
                        </div>
                        
                        <!-- Nút hành động -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-end gap-1">
                            <x-admin.button href="{{ route('admin.languages.index') }}" variant="secondary">
                                <span>Quay lại</span>
                            </x-admin.button>
                            <x-admin.button type="submit" name="submit_action" value="save" variant="primary">
                                <span>Lưu</span>
                            </x-admin.button>
                            <x-admin.button type="submit" name="submit_action" value="save_and_edit" variant="outline">
                                <span>Lưu & Sửa</span>
                            </x-admin.button>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selector = document.getElementById('iso_language_selector');
        if (!selector) return;

        const inputCode = document.querySelector('input[name="code"]');
        const inputName = document.querySelector('input[name="name"]');
        const inputNativeName = document.querySelector('input[name="native_name"]');
        const inputFlag = document.querySelector('input[name="flag"]');

        selector.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption || !selectedOption.value) return;

            const code = selectedOption.value;
            const data = JSON.parse(selectedOption.dataset.info || '{}');

            if (inputCode) inputCode.value = code;
            if (inputName) inputName.value = data.name || '';
            if (inputNativeName) inputNativeName.value = data.native_name || '';
            if (inputFlag) inputFlag.value = data.flag || '';
        });
    });
</script>
@endpush
@endsection
