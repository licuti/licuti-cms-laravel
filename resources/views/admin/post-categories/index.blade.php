@extends('layouts.admin')
@section('title', __('Quản lý Danh mục Bài viết'))

@section('content')
<div class="space-y-4">
    <x-admin.page-header
        title="{{ __('Danh sách Danh mục Bài viết') }}"
        subtitle="{{ __('Quản lý cây danh mục bài viết, thứ tự và trạng thái hiển thị') }}"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Danh mục Bài viết')],
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('admin.post-categories.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>{{ __('Thêm danh mục') }}</span>
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @if(!empty($stats))
        <div class="d-flex flex-wrap gap-2">
            <span class="badge text-bg-light border d-inline-flex align-items-center gap-1 px-3 py-2">
                <span class="text-body-secondary small">{{ __('Tổng:') }}</span>
                <span class="fw-semibold">{{ $stats['total'] }}</span>
            </span>
            <span class="badge text-bg-success bg-opacity-10 text-success border border-success border-opacity-25 d-inline-flex align-items-center gap-1 px-3 py-2">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span class="small">{{ __('Hoạt động:') }}</span>
                <span class="fw-semibold">{{ $stats['active'] }}</span>
            </span>
            <span class="badge text-bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 d-inline-flex align-items-center gap-1 px-3 py-2">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                <span class="small">{{ __('Đã ẩn:') }}</span>
                <span class="fw-semibold">{{ $stats['inactive'] }}</span>
            </span>
        </div>
    @endif

    <div class="row g-4">
        <!-- Cột Trái: Thêm mới -->
        <div class="col-lg-4">
            @include('admin.post-categories._quick_form')
        </div>

        <!-- Cột Phải: Danh sách -->
        <div class="col-lg-8">
            <x-admin.card>
                <x-slot:action>
                    <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3 w-100">
                        <!-- Bulk Actions -->
                        <div class="d-flex align-items-center gap-2">
                            <x-admin.select id="bulk-action-select" class="w-auto fw-medium" size="sm">
                                <option value="">{{ __('Hành động hàng loạt...') }}</option>
                                @foreach($bulkActions as $actionKey => $actionLabel)
                                    <option value="{{ $actionKey }}">{{ $actionLabel }}</option>
                                @endforeach
                            </x-admin.select>
                            <x-admin.button type="button" id="btn-apply-bulk" variant="secondary" size="sm">{{ __('Áp dụng') }}</x-admin.button>
                        </div>

                        <!-- Search & Filter -->
                        <form action="{{ route('admin.post-categories.index') }}" method="GET"
                              class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 flex-grow-1 justify-content-end">
                            <div class="flex-grow-1" style="min-width:180px;max-width:280px;">
                                <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}"
                                               placeholder="{{ __('Tìm tên, slug danh mục...') }}" size="sm">
                                    <x-slot:prefix>
                                        <svg class="text-body-secondary" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </x-slot:prefix>
                                </x-admin.input>
                            </div>
                            <x-admin.select name="status" class="w-auto" size="sm">
                                <option value="">{{ __('Tất cả trạng thái') }}</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>{{ __('Hoạt động') }}</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>{{ __('Đã ẩn') }}</option>
                            </x-admin.select>
                            <x-admin.button type="submit" variant="secondary" size="sm">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                <span>{{ __('Lọc') }}</span>
                            </x-admin.button>
                            @if(request()->hasAny(['keyword', 'status']))
                                <x-admin.button href="{{ route('admin.post-categories.index') }}" variant="outline-secondary" size="sm">{{ __('Xóa') }}</x-admin.button>
                            @endif
                        </form>
                    </div>
                </x-slot:action>

                <!-- Hidden Bulk Action Form -->
                <form action="{{ route('admin.post-categories.bulk') }}" method="POST" id="form-bulk-action" class="d-none">
                    @csrf
                    <input type="hidden" name="bulk_module" value="post_categories">
                    <input type="hidden" name="action" id="bulk-action-input">
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <x-admin.table-th align="center" padding="px-3" width="10"><input type="checkbox" id="check-all" class="form-check-input"></x-admin.table-th>
                                <x-admin.table-th>{{ __('Danh mục') }}</x-admin.table-th>
                                @foreach($activeLanguages as $lang)
                                    <x-admin.table-th align="center" padding="px-2" title="{{ $lang->native_name ?: $lang->name }}" width="60">
                                        @if($lang->flag_url)
                                            <img src="{{ $lang->flag_url }}" alt="{{ $lang->code }}" class="rounded shadow-sm" style="width:1.5rem;height:1rem;object-fit:cover;" onerror="this.outerHTML='<span class=\'text-body-secondary fw-bold\' style=\'font-size:0.65rem;\'>{{ strtoupper($lang->code) }}</span>'">
                                        @else
                                            <span class="text-body-secondary fw-bold" style="font-size:0.65rem;">{{ strtoupper($lang->code) }}</span>
                                        @endif
                                    </x-admin.table-th>
                                @endforeach
                                <x-admin.table-th align="center" width="80">{{ __('Thứ tự') }}</x-admin.table-th>
                                <x-admin.table-th align="center" width="120">{{ __('Trạng thái') }}</x-admin.table-th>
                                <x-admin.table-th width="140">{{ __('Ngày tạo') }}</x-admin.table-th>
                            </tr>
                        </thead>
                        <tbody id="post-categories-table-body">
                            @forelse($postCategories ?? [] as $postCategory)
                                @php
                                    $actions = [
                                        ['label' => __('Sửa'), 'route' => route('admin.post-categories.edit', $postCategory->uuid ?? $postCategory->id), 'method' => 'GET', 'color' => 'blue'],
                                        [
                                            'label'         => __('Xóa'),
                                            'route'         => route('admin.post-categories.destroy', $postCategory->uuid ?? $postCategory->id),
                                            'method'        => 'DELETE',
                                            'color'         => 'red',
                                            'confirm_title' => __('Xóa danh mục?'),
                                            'confirm_text'  => __('Thao tác này không thể hoàn tác. Danh mục con sẽ được đẩy lên một cấp.'),
                                            'confirm_btn'   => __('Xóa ngay')
                                        ]
                                    ];
                                @endphp
                                <tr>
                                    <td class="text-center px-3">
                                        <input type="checkbox" name="ids[]" value="{{ $postCategory->uuid ?? $postCategory->id }}" class="row-checkbox form-check-input">
                                    </td>
                                    <td class="px-3">
                                        <x-admin.table-cell-primary
                                            :image="$postCategory->image_url"
                                            :title="$postCategory->tree_name ?? $postCategory->translated_name"
                                            :subtitle="$postCategory->translated_slug"
                                            :actions="$actions"
                                            image-shape="rounded"
                                        />
                                    </td>
                                    @foreach($activeLanguages as $lang)
                                        @php $hasTrans = $postCategory->translations->contains('locale', $lang->code); @endphp
                                        <td class="text-center px-2">
                                            <a href="{{ route('admin.post-categories.edit', ['uuid' => $postCategory->uuid, 'lang' => $lang->code]) }}"
                                               class="d-inline-flex align-items-center justify-content-center rounded border {{ $hasTrans ? 'text-primary border-primary-subtle bg-primary bg-opacity-10' : 'text-body-secondary border-secondary-subtle bg-body-tertiary' }}"
                                               style="width:1.75rem;height:1.75rem;"
                                               title="{{ $hasTrans ? __('Sửa bản dịch') : __('Thêm bản dịch') }} {{ $lang->name }}">
                                                @if($hasTrans)
                                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                @else
                                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                                @endif
                                            </a>
                                        </td>
                                    @endforeach
                                    <td class="text-center px-3 text-body-secondary small">{{ $postCategory->display_order ?? 0 }}</td>
                                    <td class="text-center px-3">
                                        @if($postCategory->is_active ?? true)
                                            <x-admin.badge label="{{ __('Hoạt động') }}" color="green" />
                                        @else
                                            <x-admin.badge label="{{ __('Đã ẩn') }}" color="red" />
                                        @endif
                                    </td>
                                    <td class="px-3 text-body-secondary small">{{ optional($postCategory->created_at)->format('d/m/Y H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 5 + count($activeLanguages ?? []) }}" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center gap-2 text-body-secondary">
                                            <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                            <p class="mb-0 fw-medium">{{ __('Chưa có danh mục nào') }}</p>
                                            <p class="mb-0 small">{{ __('Bấm "Thêm danh mục" hoặc dùng form bên trái để tạo mới.') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        </div>
    </div>
</div>
@endsection
