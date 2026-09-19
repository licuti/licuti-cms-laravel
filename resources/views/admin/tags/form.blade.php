@extends('layouts.admin')

@php
    $tag = $tag ?? null;
    $isEdit = !is_null($tag);
@endphp

@section('title', $isEdit ? __('Sửa Thẻ Tag: :name', ['name' => $tag->name]) : __('Thêm Thẻ Tag mới'))

@section('content')
    {{-- Tiêu đề trang --}}
    <x-admin.page-header
        :title="$isEdit ? __('Sửa Thẻ Tag') : __('Thêm Thẻ Tag mới')"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Thẻ Tag'), 'url' => route('admin.tags.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')],
        ]"
    />

    {{-- Form soạn thảo chính --}}
    <form
        id="tag-form"
        action="{{ $isEdit ? route('admin.tags.update', $tag->uuid) : route('admin.tags.store') }}"
        method="POST"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="row align-items-start g-4">

            {{-- Cột Trái: Thông tin thẻ tag --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">
                <x-admin.card title="{{ __('Thông tin Thẻ Tag') }}">
                    {{-- Tên thẻ --}}
                    <x-admin.form-group
                        label="{{ __('Tên thẻ tag') }}"
                        name="name"
                        required
                    >
                        <x-admin.input
                            type="text"
                            name="name"
                            size="sm"
                            value="{{ old('name', $tag?->name ?? '') }}"
                            placeholder="{{ __('Ví dụ: Khuyến mãi, Công nghệ, Thời trang...') }}"
                            class="seo-source-name"
                            required
                        />
                    </x-admin.form-group>

                    {{-- Slug --}}
                    <x-admin.form-group
                        label="{{ __('Đường dẫn (Slug)') }}"
                        name="slug"
                        description="{{ __('Để trống hệ thống sẽ tự động tạo từ tên thẻ.') }}"
                        class="mb-0"
                    >
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-secondary text-body-secondary">{{ url('/tags') }}/</span>
                            <x-admin.input
                                type="text"
                                name="slug"
                                size="sm"
                                value="{{ old('slug', $tag?->slug ?? '') }}"
                                placeholder="khuyen-mai"
                                class="seo-source-slug"
                            />
                        </div>
                    </x-admin.form-group>
                </x-admin.card>
            </div>

            {{-- Cột Phải: Sidebar cài đặt --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">
                <x-admin.card title="{{ __('Cài đặt & Xuất bản') }}">
                    {{-- Thứ tự hiển thị --}}
                    <x-admin.form-group
                        label="{{ __('Thứ tự hiển thị') }}"
                        name="display_order"
                        description="{{ __('Số nhỏ hơn sẽ hiển thị trước.') }}"
                        class="mb-3"
                    >
                        <x-admin.input
                            type="number"
                            name="display_order"
                            size="sm"
                            min="0"
                            value="{{ old('display_order', $tag?->display_order ?? 0) }}"
                        />
                    </x-admin.form-group>

                    @if($isEdit)
                        <div class="small text-body-secondary mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <span>{{ __('Số bài viết:') }}</span>
                                <span class="fw-semibold text-body">{{ $tag->posts()->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <span>{{ __('Ngày tạo:') }}</span>
                                <span class="text-body">{{ $tag->created_at?->format('d/m/Y H:i') ?? '---' }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Nút lưu --}}
                    <div class="d-flex gap-2 pt-2 border-top">
                        <x-admin.button href="{{ route('admin.tags.index') }}" variant="outline-secondary" size="sm" class="flex-grow-1">
                            {{ __('Quay lại') }}
                        </x-admin.button>
                        <x-admin.button type="submit" name="submit_action" value="save" variant="primary" size="sm" class="flex-grow-1">
                            {{ __('Lưu') }}
                        </x-admin.button>
                    </div>
                    <div class="pt-2">
                        <x-admin.button type="submit" name="submit_action" value="save_and_edit" variant="secondary" size="sm" class="w-100 justify-content-center">
                            {{ __('Lưu & Tiếp tục sửa') }}
                        </x-admin.button>
                    </div>
                </x-admin.card>
            </div>
        </div>
    </form>

    <x-admin.scripts.auto-slug :is-edit="$isEdit" />
@endsection