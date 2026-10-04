@extends('layouts.admin')

@php
    $valueModel = $valueModel ?? null;
    $isEdit = !is_null($valueModel);
@endphp

@section('title', $isEdit ? __('Sửa giá trị thuộc tính') : __('Thêm giá trị thuộc tính'))

@section('content')
    <x-admin.page-header 
        :title="$isEdit ? __('Sửa giá trị thuộc tính') : __('Thêm giá trị thuộc tính')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Thuộc tính sản phẩm'), 'url' => route('admin.product-attributes.index')],
            ['label' => $attribute->name, 'url' => route('admin.product-attributes.values.index', $attribute->uuid)],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')]
        ]" 
    />

    <form 
        action="{{ $isEdit ? route('admin.product-attributes.values.update', [$attribute->uuid, $valueModel->uuid]) : route('admin.product-attributes.values.store', $attribute->uuid) }}" 
        method="POST"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row align-items-start g-4">
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">
                
                @if(isset($activeLanguages) && $activeLanguages->count() > 1)
                    <x-admin.lang-tabs :active-languages="$activeLanguages" :default-locale="$defaultLocale" />
                @endif

                @foreach($activeLanguages as $lang)
                    @php 
                        $code = $lang->code ?? app()->getLocale(); 
                        $panelClass = ($code === $defaultLocale) ? '' : 'd-none'; 
                        $translation = $valueModel?->translate($code);
                    @endphp

                    <div class="lang-panel {{ $panelClass }} d-flex flex-column gap-4" data-lang-panel="{{ $code }}">
                        <x-admin.card>
                            <x-admin.form-group 
                                :label="__('Giá trị')" 
                                name="translations.{{ $code }}.value" 
                                :required="$code === $defaultLocale"
                            >
                                <x-admin.input 
                                    type="text" 
                                    name="translations[{{ $code }}][value]" 
                                    size="sm"
                                    value="{{ old('translations.'.$code.'.value', $translation?->value ?? '') }}" 
                                    placeholder="{{ __('Ví dụ: Đỏ, Xanh, S, M, L...') }}" 
                                    :required="$code === $defaultLocale"
                                />
                            </x-admin.form-group>
                        </x-admin.card>
                    </div>
                @endforeach
            </div>

            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">
                <x-admin.card :title="__('Thao tác')">
                    <div class="d-flex flex-column gap-2">
                        <button type="submit" name="submit_action" value="save" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-check-circle me-1"></i> {{ $isEdit ? __('Cập nhật') : __('Lưu') }}
                        </button>
                        <button type="submit" name="submit_action" value="save_and_edit" class="btn btn-outline-primary btn-sm w-100">
                            <i class="bi bi-pencil-square me-1"></i> {{ __('Lưu & tiếp tục sửa') }}
                        </button>
                        <a href="{{ route('admin.product-attributes.values.index', $attribute->uuid) }}" class="btn btn-outline-secondary btn-sm w-100">
                            {{ __('Quay lại') }}
                        </a>
                    </div>
                </x-admin.card>

                <x-admin.card :title="__('Cấu hình')">
                    @if($attribute->type === \App\Enums\AttributeType::COLOR->value)
                        <x-admin.form-group :label="__('Mã màu')" name="color_code" class="mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <x-admin.input 
                                    type="color" 
                                    class="p-1"
                                    style="width:40px;"
                                    value="{{ old('color_code', $valueModel?->color_code ?? '#000000') }}" 
                                    onchange="this.nextElementSibling.value = this.value"
                                />
                                <x-admin.input 
                                    type="text" 
                                    name="color_code" 
                                    size="sm"
                                    class="font-monospace"
                                    value="{{ old('color_code', $valueModel?->color_code ?? '#000000') }}" 
                                    oninput="this.previousElementSibling.value = this.value"
                                />
                            </div>
                        </x-admin.form-group>
                    @endif

                    <x-admin.form-group :label="__('Thứ tự sắp xếp')" name="display_order" class="mb-0">
                        <x-admin.input 
                            type="number" 
                            name="display_order" 
                            size="sm"
                            value="{{ old('display_order', $valueModel?->display_order ?? 0) }}" 
                            min="0"
                        />
                    </x-admin.form-group>
                </x-admin.card>
            </div>
        </div>
    </form>
@endsection
