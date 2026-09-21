@extends('layouts.admin')
@section('title', __('Quản lý Trang tĩnh'))

@section('content')
    <x-admin.page-header 
        title="{{ __('Danh sách Trang tĩnh') }}" 
        subtitle="{{ __('Quản lý các trang tĩnh của website') }}" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Trang tĩnh')]
        ]">
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.pages.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>{{ __('Thêm mới') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Tab Navigation --}}
    <x-admin.filter-tabs :items="$tabs" :current="$tab" route="admin.pages.index" />

    {{-- Bulk Actions + Search/Filter Bar --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
        <div class="d-flex align-items-center gap-2">
            <x-admin.select id="bulk-action-select" class="w-auto fw-medium" size="sm">
                <option value="">{{ __('Hành động hàng loạt...') }}</option>
                @foreach($bulkActions as $actionKey => $actionLabel)
                    <option value="{{ $actionKey }}">{{ $actionLabel }}</option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="button" id="btn-apply-bulk" variant="primary" class="flex-shrink-0" size="sm">{{ __('Áp dụng') }}</x-admin.button>
        </div>

        {{-- Search & Filter Form --}}
        <form action="{{ route('admin.pages.index') }}" method="GET" id="form-filter-pages" class="d-flex align-items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="{{ __('Tìm tiêu đề...') }}" size="sm" style="min-width:260px;" />
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('Lọc') }}</x-admin.button>
            @if(request()->has('keyword') || request()->has('tab'))
                <x-admin.button href="{{ route('admin.pages.index') }}" variant="outline-secondary" size="sm">{{ __('Xóa bộ lọc') }}</x-admin.button>
            @endif
        </form>
    </div>

    {{-- Hidden Bulk Action Form --}}
    <form id="form-bulk-action" action="{{ route('admin.pages.bulk') }}" method="POST">
        @csrf
        <input type="hidden" name="bulk_module" value="pages">
        <input type="hidden" name="action" id="bulk-action-input" value="">
    </form>

    <x-admin.table>
        <x-slot:head>
            <x-admin.table-th padding="text-center" align="center" width="40px">
                <input type="checkbox" id="check-all" class="form-check-input">
            </x-admin.table-th>
            <x-admin.table-th>{{ __('Tiêu đề') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Thứ tự') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Trạng thái') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Ngày tạo') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($pages as $page)
            @php
                $title = $page->title;

                // Thụt lề theo cấp cây + icon phân biệt node có con / node lá
                $depth    = (int) ($page->depth ?? 0);
                $hasKids  = (bool) ($page->has_children ?? false);
                $indent   = $depth > 0 ? str_repeat('&nbsp;&nbsp;&nbsp;', $depth) : '';
                $treeIcon = $hasKids
                    ? '<span class="text-body-secondary me-1" aria-hidden="true">&#9662;</span>'
                    : '<span class="text-body-tertiary me-1" aria-hidden="true">&bull;</span>';

                $actions = [
                    ['label' => __('Sửa'), 'route' => route('admin.pages.edit', $page->uuid), 'color' => 'blue'],
                    [
                        'label'         => __('Xóa'),
                        'route'         => route('admin.pages.destroy', $page->uuid),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => __('Xóa trang tĩnh?'),
                        'confirm_text'  => __('Bạn có chắc chắn muốn xóa trang này?'),
                        'confirm_btn'   => __('Xóa ngay')
                    ],
                ];
            @endphp
            <tr class="group">
                <td class="py-3 px-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $page->id }}" class="row-checkbox form-check-input">
                </td>
                <td class="py-3 px-3">
                    <div class="d-flex align-items-center">
                        <span class="text-body-tertiary" style="white-space: pre;">{!! $indent !!}</span>
                        {!! $treeIcon !!}
                        <x-admin.table-cell-primary :title="$title" :actions="$actions" />
                    </div>
                </td>
                <td class="py-3 px-3 text-center small text-body-secondary">
                    {{ $page->display_order ?? 0 }}
                </td>
                <td class="py-3 px-3">
                    <x-admin.badge :label="$page->status_label" :color="$page->status_color" />
                </td>
                <td class="py-3 px-3 small text-body-secondary">
                    {{ $page->created_at?->format('d/m/Y') ?? '---' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-4">
                    {{ __('Không tìm thấy trang tĩnh nào phù hợp.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection