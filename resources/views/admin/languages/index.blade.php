@extends('layouts.admin')

@section('title', 'Quản lý Ngôn ngữ')

@section('content')
    <!-- Page Header Component -->
    <x-admin.page-header 
        title="Ngôn ngữ Hệ thống" 
        subtitle="Quản lý danh sách ngôn ngữ hiển thị trên website"
        :breadcrumbs="[['label' => 'Bảng điều khiển', 'url' => route('admin.dashboard')], ['label' => 'Ngôn ngữ']]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.languages.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Thêm Ngôn ngữ</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- Filter, Search & Bulk Actions -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <!-- Bulk Actions -->
        <div class="d-flex align-items-center gap-2">
            <x-admin.select id="bulk-action-select" size="sm" style="width: auto;">
                <option value="">{{ __('Hành động hàng loạt...') }}</option>
                <option value="delete">{{ __('Xóa đã chọn') }}</option>
                <option value="status_active">{{ __('Bật hoạt động') }}</option>
                <option value="status_inactive">{{ __('Tắt hoạt động') }}</option>
            </x-admin.select>
            <x-admin.button type="button" id="btn-apply-bulk" variant="primary" size="sm" class="flex-shrink-0">
                {{ __('Áp dụng') }}
            </x-admin.button>
        </div>

        <!-- Search & Filter Form -->
        <form action="{{ route('admin.languages.index') }}" method="GET" id="form-filter-languages" class="d-flex flex-wrap align-items-center gap-2">
            <div style="min-width: 220px;">
                <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm mã, tên ngôn ngữ..." size="sm" />
            </div>
            
            <x-admin.select name="status" size="sm" style="width: auto;">
                <option value="">Tất cả trạng thái</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Đã tắt</option>
            </x-admin.select>
            
            <x-admin.button type="submit" variant="primary" size="sm">Lọc</x-admin.button>
            @if(request()->hasAny(['keyword', 'status']))
                <x-admin.button href="{{ route('admin.languages.index') }}" variant="outline" size="sm">Xóa lọc</x-admin.button>
            @endif
        </form>
    </div>

    <!-- Hidden Bulk Action Form -->
    <form action="#" method="POST" id="form-bulk-action" style="display: none;">
        @csrf
        <input type="hidden" name="action" id="bulk-action-input">
    </form>

    <!-- Languages Table -->
    <x-admin.table tbodyId="languages-table-body" :paginator="$languages">
        <x-slot:head>
            <th style="width:40px;" class="text-center">
                <input type="checkbox" id="check-all" class="form-check-input">
            </th>
            <th class="small">Ngôn ngữ</th>
            <th class="small">Mã (Code)</th>
            <th class="small text-center" style="width:80px;">Thứ tự</th>
            <th class="small">Mặc định</th>
            <th class="small">Trạng thái</th>
        </x-slot:head>

        @forelse($languages as $language)
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="ids[]" value="{{ $language->id }}" class="row-checkbox form-check-input" {{ $language->is_default ? 'disabled title="Không thể thao tác hàng loạt ngôn ngữ mặc định"' : '' }}>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        @if($language->flag_url)
                            <img src="{{ $language->flag_url }}" alt="{{ $language->name }}" class="rounded-1 border object-fit-cover shadow-sm" style="width: 2rem; height: 1.4rem;">
                        @else
                            <div class="rounded-1 bg-body-secondary border d-flex align-items-center justify-content-center text-body-tertiary small" style="width: 2rem; height: 1.4rem;">
                                -
                            </div>
                        @endif
                        <div>
                            <div class="fw-semibold text-body">{{ $language->name }}</div>
                            <div class="small text-body-secondary">{{ $language->native_name }}</div>
                            
                            @php
                                $actions = [
                                    [
                                        'label' => 'Sửa',
                                        'route' => route('admin.languages.edit', $language->code),
                                        'method' => 'GET',
                                        'color' => 'blue'
                                    ]
                                ];
                                if (!$language->is_default) {
                                    $actions[] = [
                                        'label' => 'Xóa',
                                        'route' => route('admin.languages.destroy', $language->code),
                                        'method' => 'DELETE',
                                        'color' => 'red',
                                        'confirm_title' => 'Xóa ngôn ngữ?',
                                        'confirm_text' => 'Bạn có chắc chắn muốn xóa ngôn ngữ này không? Thao tác không thể hoàn tác.',
                                        'confirm_btn' => 'Xóa ngay'
                                    ];
                                }
                            @endphp
                            <x-admin.row-actions :actions="$actions" />
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge text-bg-secondary bg-opacity-10 text-body border border-secondary border-opacity-25 font-monospace text-uppercase">
                        {{ $language->code }}
                    </span>
                </td>
                <td class="text-center small text-body-secondary">
                    {{ $language->display_order ?? 0 }}
                </td>
                <td>
                    @if($language->is_default)
                        <x-admin.badge label="Mặc định" color="primary" />
                    @else
                        <span class="text-body-tertiary">-</span>
                    @endif
                </td>
                <td>
                    @if($language->is_active)
                        <x-admin.badge label="Hoạt động" color="success" />
                    @else
                        <x-admin.badge label="Đã tắt" color="secondary" />
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-4 text-body-secondary">
                    Không tìm thấy ngôn ngữ nào phù hợp.
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection
