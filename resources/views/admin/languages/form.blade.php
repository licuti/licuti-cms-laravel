@extends('layouts.admin')

@php
    $isEdit = isset($language);
    $actionUrl = $isEdit ? route('admin.languages.update', $language->code) : route('admin.languages.store');
    $title = $isEdit ? 'Cập nhật Ngôn ngữ' : 'Thêm Ngôn ngữ';
    $subtitle = $isEdit ? 'Chỉnh sửa thông tin ngôn ngữ' : 'Khởi tạo ngôn ngữ mới cho hệ thống';
@endphp

@section('title', $title)

@section('content')
    <x-admin.page-header 
        :title="$title" 
        :subtitle="$subtitle"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => route('admin.dashboard')], 
            ['label' => 'Ngôn ngữ', 'url' => route('admin.languages.index')],
            ['label' => $isEdit ? 'Cập nhật' : 'Thêm mới']
        ]"
    />

    <form action="{{ $actionUrl }}" method="POST">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row g-4 align-items-start">
            <!-- Cột Trái (8 phần): Thông tin Ngôn ngữ -->
            <div class="col-12 col-lg-8">
                <x-admin.card title="Thông tin Ngôn ngữ">
                    <div class="d-flex flex-column gap-3">
                        @if(!$isEdit && !empty($isoLanguages))
                            <div class="p-3 bg-body-tertiary border rounded mb-2">
                                <x-admin.form-group label="Chọn nhanh từ chuẩn ISO" name="iso_selector" description="Tự động điền mã, tên và cờ quốc gia. Bạn vẫn có thể chỉnh sửa lại sau khi chọn.">
                                    <x-admin.select id="iso_language_selector" name="iso_selector" size="sm">
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

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <x-admin.form-group label="Mã ngôn ngữ (Code)" name="code" required>
                                    <x-admin.input type="text" name="code" value="{{ old('code', $language->code ?? '') }}" required placeholder="VD: vi, en, ja..." size="sm" />
                                </x-admin.form-group>
                            </div>

                            <div class="col-12 col-md-6">
                                <x-admin.form-group label="Thứ tự hiển thị" name="display_order" description="Thứ tự xuất hiện trên web (tăng dần).">
                                    <x-admin.input type="number" name="display_order" value="{{ old('display_order', $language->display_order ?? 0) }}" min="0" size="sm" />
                                </x-admin.form-group>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <x-admin.form-group label="Tên hiển thị" name="name" required>
                                    <x-admin.input type="text" name="name" value="{{ old('name', $language->name ?? '') }}" required placeholder="VD: Vietnamese" size="sm" />
                                </x-admin.form-group>
                            </div>

                            <div class="col-12 col-md-6">
                                <x-admin.form-group label="Tên bản địa" name="native_name" required>
                                    <x-admin.input type="text" name="native_name" value="{{ old('native_name', $language->native_name ?? '') }}" required placeholder="VD: Tiếng Việt" size="sm" />
                                </x-admin.form-group>
                            </div>
                        </div>

                        <x-admin.form-group label="Cờ quốc gia (Flag)" name="flag" description="Có thể chọn ảnh cờ từ thư viện Media. Nếu để trống sẽ sử dụng tự động (từ CDN).">
                            <div style="max-width: 200px;">
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

            <!-- Cột Phải (4 phần): Trạng thái -->
            <div class="col-12 col-lg-4">
                <x-admin.card title="Cấu hình & Trạng thái">
                    <div class="d-flex flex-column gap-3">
                        @php
                            $isDefault = $isEdit && $language->is_default;
                        @endphp
                        
                        <!-- Ngôn ngữ Mặc định -->
                        <x-admin.toggle 
                            name="is_default" 
                            label="Ngôn ngữ mặc định" 
                            :description="$isDefault ? 'Đang là mặc định. Hãy set ngôn ngữ khác làm mặc định để gỡ bỏ.' : ''"
                            :checked="old('is_default', $language->is_default ?? false)"
                            :readonly="$isDefault"
                        />

                        <!-- Trạng thái Hoạt động -->
                        <div class="pt-3 border-top">
                            <x-admin.toggle 
                                name="is_active" 
                                label="Trạng thái (Active)" 
                                description="Bật để hiển thị ngôn ngữ này trên website."
                                :checked="old('is_active', $language->is_active ?? true)"
                                :readonly="$isDefault"
                            />
                        </div>
                        
                        <!-- Nút hành động -->
                        <div class="pt-3 border-top d-flex flex-wrap align-items-center justify-content-end gap-2">
                            <x-admin.button href="{{ route('admin.languages.index') }}" variant="secondary" size="sm">
                                <span>Quay lại</span>
                            </x-admin.button>
                            <x-admin.button type="submit" name="submit_action" value="save" variant="primary" size="sm">
                                <span>Lưu</span>
                            </x-admin.button>
                            <x-admin.button type="submit" name="submit_action" value="save_and_edit" variant="outline" size="sm">
                                <span>Lưu & Sửa</span>
                            </x-admin.button>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selector = document.getElementById('iso_language_selector');
        if (!selector) return;

        const inputCode = document.querySelector('input[name="code"]');
        const inputName = document.querySelector('input[name="name"]');
        const inputNativeName = document.querySelector('input[name="native_name"]');

        selector.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption || !selectedOption.value) return;

            const code = selectedOption.value;
            const data = JSON.parse(selectedOption.dataset.info || '{}');

            if (inputCode) inputCode.value = code;
            if (inputName) inputName.value = data.name || '';
            if (inputNativeName) inputNativeName.value = data.native_name || '';
        });
    });
</script>
@endpush
@endsection
