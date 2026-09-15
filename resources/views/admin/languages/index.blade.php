@extends('layouts.admin')

@section('title', 'Quản lý Ngôn ngữ')

@section('content')
<div class="space-y-4">
    <!-- Page Header Component -->
    <x-admin.page-header 
        title="Ngôn ngữ Hệ thống" 
        subtitle="Quản lý danh sách ngôn ngữ hiển thị trên website"
        :breadcrumbs="[['label' => 'Bảng điều khiển', 'url' => route('admin.dashboard')], ['label' => 'Ngôn ngữ']]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.languages.create') }}" variant="primary" class="shrink-0">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Thêm Ngôn ngữ</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <!-- Filter, Search & Bulk Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Bulk Actions -->
        <div class="flex items-center gap-2">
            <x-admin.select id="bulk-action-select" class="w-auto font-medium" size="sm">
                <option value="">Hành động hàng loạt...</option>
                <option value="delete">Xóa đã chọn</option>
                <option value="status_active">Bật hoạt động</option>
                <option value="status_inactive">Tắt hoạt động</option>
            </x-admin.select>
            <x-admin.button type="button" id="btn-apply-bulk" variant="secondary" class="shrink-0" size="sm">Áp dụng</x-admin.button>
        </div>

        <!-- Search & Filter Form -->
        <form action="{{ route('admin.languages.index') }}" method="GET" id="form-filter-languages" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-1 md:max-w-xl justify-end">
            <div class="flex-1 min-w-[200px]">
                <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm mã, tên ngôn ngữ..." size="sm">
                    <x-slot:prefix>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </x-slot:prefix>
                </x-admin.input>
            </div>
            
            <x-admin.select name="status" class="w-auto" size="sm">
                <option value="">Tất cả trạng thái</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Đã tắt</option>
            </x-admin.select>
            
            <x-admin.button type="submit" variant="secondary" class="shrink-0" size="sm">Lọc</x-admin.button>
            @if(request()->hasAny(['keyword', 'status']))
                <x-admin.button href="{{ route('admin.languages.index') }}" variant="outline" size="sm" class="shrink-0">Xóa</x-admin.button>
            @endif
        </form>
    </div>

    <!-- Hidden Bulk Action Form (Chưa xử lý route, chỉ tạo mockup UI) -->
    <form action="#" method="POST" id="form-bulk-action" style="display: none;">
        @csrf
        <input type="hidden" name="action" id="bulk-action-input">
    </form>

    <!-- Languages Table -->
    <x-admin.table tbodyId="languages-table-body" :paginator="$languages">
        <x-slot:head>
            <x-admin.table-th align="center" padding="px-4" width="10"><input type="checkbox" id="check-all" class="w-4 h-4 rounded text-blue-600 focus:ring-0 cursor-pointer"></x-admin.table-th>
            <x-admin.table-th>Ngôn ngữ / Thao tác</x-admin.table-th>
            <x-admin.table-th>Mã (Code)</x-admin.table-th>
            <x-admin.table-th>Mặc định</x-admin.table-th>
            <x-admin.table-th>Trạng thái</x-admin.table-th>
        </x-slot:head>

        @forelse($languages as $language)
            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                <td class="py-3 px-4 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $language->id }}" class="language-checkbox w-4 h-4 rounded text-blue-600 focus:ring-0 cursor-pointer" {{ $language->is_default ? 'disabled title="Không thể thao tác hàng loạt ngôn ngữ mặc định"' : '' }}>
                </td>
                <td class="py-3 px-6">
                    <div class="flex items-center gap-3">
                        @if($language->flag_url)
                            <img src="{{ $language->flag_url }}" alt="{{ $language->name }}" class="w-8 h-auto object-contain rounded-sm border border-slate-200 dark:border-slate-700">
                        @else
                            <div class="w-8 h-8 rounded-sm bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 border border-slate-200 dark:border-slate-700">
                                -
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm">
                                {{ $language->name }}
                            </p>
                            <p class="text-xs text-slate-500">{{ $language->native_name }}</p>
                            
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
                <td class="py-3 px-6">
                    <span class="bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded text-xs uppercase font-semibold border border-slate-200 dark:border-slate-700">
                        {{ $language->code }}
                    </span>
                </td>
                <td class="py-3 px-6">
                    @if($language->is_default)
                        <x-admin.badge label="Mặc định" color="blue" />
                    @else
                        <span class="text-slate-400">-</span>
                    @endif
                </td>
                <td class="py-3 px-6">
                    @if($language->is_active)
                        <x-admin.badge label="Hoạt động" color="green" />
                    @else
                        <x-admin.badge label="Đã tắt" color="slate" />
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-8 text-slate-500">
                    Không tìm thấy ngôn ngữ nào phù hợp.
                </td>
            </tr>
        @endforelse
    </x-admin.table>
</div>
@endsection
