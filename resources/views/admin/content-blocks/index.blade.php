@extends('layouts.admin')

@section('title', __('Quản lý Khối nội dung'))

@section('content')
    <x-admin.page-header
        title="{{ __('Danh sách Khối nội dung') }}"
        subtitle="{{ __('Tạo các mảnh nội dung HTML có thể tái sử dụng trong theme.') }}"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Khối nội dung')]
        ]"
    >
        <x-slot:actions>
            @can('content-blocks.create')
                <x-admin.button href="{{ route('admin.content-blocks.create') }}" variant="primary" class="flex-shrink-0">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>{{ __('Thêm mới') }}</span>
                </x-admin.button>
            @endcan
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filter-tabs :items="$tabs" :current="$tab" route="admin.content-blocks.index" />

    <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
        <div class="d-flex align-items-center gap-2">
            @can('content-blocks.update')
                <x-admin.select id="bulk-action-select" class="w-auto fw-medium" size="sm">
                    <option value="">{{ __('Hành động hàng loạt...') }}</option>
                    @foreach($bulkActions as $actionKey => $actionLabel)
                        <option value="{{ $actionKey }}">{{ $actionLabel }}</option>
                    @endforeach
                </x-admin.select>
                <x-admin.button type="button" id="btn-apply-bulk" variant="primary" size="sm">{{ __('Áp dụng') }}</x-admin.button>
            @endcan
        </div>

        <form action="{{ route('admin.content-blocks.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="{{ __('Tìm tên, slug, UUID...') }}" size="sm" style="min-width:280px;" />
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('Lọc') }}</x-admin.button>
        </form>
    </div>

    <form id="form-bulk-action" action="{{ route('admin.content-blocks.bulk') }}" method="POST">
        @csrf
        <input type="hidden" name="bulk_module" value="content_blocks">
        <input type="hidden" name="action" id="bulk-action-input" value="">
    </form>

    <x-admin.table :paginator="$blocks">
        <x-slot:head>
            <x-admin.table-th align="center"><input type="checkbox" id="check-all" class="form-check-input"></x-admin.table-th>
            <x-admin.table-th>{{ __('Tiêu đề') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Shortcode') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Ngôn ngữ') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Trạng thái') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Ngày tạo') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($blocks as $block)
            @php
                $translation = $block->translationFor(app()->getLocale(), config('app.fallback_locale', 'vi'));
                $actions = [];
                if (auth()->user()?->can('content-blocks.view')) {
                    $actions[] = ['label' => __('Sửa'), 'route' => route('admin.content-blocks.edit', ['uuid' => $block->uuid]), 'color' => 'blue'];
                }
                if (auth()->user()?->can('content-blocks.delete')) {
                    $actions[] = [
                        'label' => __('Xóa'),
                        'route' => route('admin.content-blocks.destroy', ['uuid' => $block->uuid]),
                        'method' => 'DELETE',
                        'color' => 'red',
                        'confirm_title' => __('Xóa khối nội dung?'),
                        'confirm_text' => __('Bạn có chắc chắn muốn xóa khối này?'),
                        'confirm_btn' => __('Xóa ngay'),
                    ];
                }
            @endphp
            <tr>
                <td class="py-3 px-3 text-center"><input type="checkbox" name="ids[]" value="{{ $block->id }}" class="row-checkbox form-check-input"></td>
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary
                        :image="$block->thumbnail_url"
                        :title="$translation?->title ?? '---'"
                        :subtitle="'/' . ($translation?->slug ?? '')"
                        :actions="$actions"
                    />
                </td>
                <td class="py-3 px-3"><code class="small user-select-all">{{ $block->shortcode }}</code></td>
                <td class="py-3 px-3">
                    <x-admin.language-flags
                        :locales="$block->translations->pluck('locale')"
                        route="admin.content-blocks.edit"
                        :route-params="['uuid' => $block->uuid]"
                    />
                </td>
                <td class="py-3 px-3"><x-admin.badge :label="$block->status_label" :color="$block->status_color" /></td>
                <td class="py-3 px-3 small text-body-secondary">{{ $block->created_at?->format('d/m/Y') ?? '---' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center py-5 text-body-secondary">{{ __('Chưa có khối nội dung nào.') }}</td></tr>
        @endforelse
    </x-admin.table>
@endsection
