@extends('layouts.admin')
@section('title', __('Quản lý Danh mục Bài viết'))

@section('content')
<div class="space-y-4">
    <x-admin.page-header
        title="{{ __('Danh sách Danh mục Bài viết') }}"
        subtitle="{{ __('Quản lý cây danh mục catalog, thứ tự và trạng thái hiển thị') }}"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Danh mục Bài viết')],
        ]"
    />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Cột Trái: Thêm mới -->
        <div class="lg:col-span-4 space-y-4">
            @include('admin.post-categories._quick_form')
        </div>

        <!-- Cột Phải: Danh sách -->
        <div class="lg:col-span-8 space-y-4">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Bulk Actions -->
        <div class="flex items-center gap-2">
            <x-admin.select id="bulk-action-select" class="w-auto font-medium" size="sm">
                <option value="">{{ __('Hành động hàng loạt...') }}</option>
                @foreach($bulkActions as $actionKey => $actionLabel)
                    <option value="{{ $actionKey }}">{{ $actionLabel }}</option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="button" id="btn-apply-bulk" variant="secondary" class="shrink-0" size="sm">{{ __('Áp dụng') }}</x-admin.button>
        </div>

        <form action="{{ route('admin.post-categories.index') }}" method="GET"
              class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-1 md:max-w-xl justify-end">
            <div class="flex-1 min-w-[200px]">
                <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}"
                               placeholder="{{ __('Tìm tên, slug danh mục...') }}" size="sm">
                    <x-slot:prefix>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
            <x-admin.button type="submit" variant="secondary" class="shrink-0" size="sm">{{ __('Lọc') }}</x-admin.button>
            @if(request()->hasAny(['keyword', 'status']))
                <x-admin.button href="{{ route('admin.post-categories.index') }}" variant="outline" size="sm" class="shrink-0">{{ __('Xóa') }}</x-admin.button>
            @endif
        </form>
    </div>

    <!-- Hidden Bulk Action Form -->
    <form action="{{ route('admin.post-categories.bulk') }}" method="POST" id="form-bulk-action" style="display: none;">
        @csrf
        <input type="hidden" name="bulk_module" value="post_categories">
        <input type="hidden" name="action" id="bulk-action-input">
    </form>

    <x-admin.table tbodyId="post-categories-table-body">
        <x-slot:head>
            <x-admin.table-th align="center" padding="px-4" width="10"><input type="checkbox" id="check-all" class="w-4 h-4 rounded text-blue-600 focus:ring-0 cursor-pointer"></x-admin.table-th>
            <x-admin.table-th>{{ __('Danh mục / Thao tác') }}</x-admin.table-th>
            @foreach($activeLanguages as $lang)
                <x-admin.table-th align="center" padding="px-2" title="{{ $lang->native_name ?: $lang->name }}" width="40">
                    @if($lang->flag_url)
                        <img src="{{ $lang->flag_url }}" alt="{{ $lang->code }}" class="w-6 h-4 object-cover mx-auto rounded-[1px] shadow-sm" onerror="this.outerHTML='<span class=\'uppercase text-[10px] font-bold text-slate-500\'>{{ $lang->code }}</span>'">
                    @else
                        <span class="uppercase text-[10px] font-bold text-slate-500">{{ $lang->code }}</span>
                    @endif
                </x-admin.table-th>
            @endforeach
            <x-admin.table-th align="center">{{ __('Thứ tự') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Trạng thái') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Ngày tạo') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($postCategories ?? [] as $postCategory)
            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                <td class="py-3 px-4 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $postCategory->uuid ?? $postCategory->id }}" class="row-checkbox w-4 h-4 rounded text-blue-600 focus:ring-0 cursor-pointer">
                </td>
                <td class="py-3 px-6">
                    <div class="flex items-center gap-3">
                        @if($postCategory->image_url)
                            <img src="{{ $postCategory->image_url }}" alt="{{ $postCategory->translated_name }}" class="w-9 h-9 rounded-lg object-cover shrink-0 shadow-sm border border-slate-200 dark:border-slate-700">
                        @else
                            <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center text-white shrink-0 shadow-sm font-bold">
                                {{ mb_substr($postCategory->translated_name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm">{{ $postCategory->tree_name ?? $postCategory->translated_name }}</p>
                            
                            @php
                                $actions = [
                                    ['label' => __('Sửa'), 'route' => route('admin.post-categories.edit', $postCategory->uuid ?? $postCategory->id), 'method' => 'GET', 'color' => 'blue'],
                                    [
                                        'label'         => __('Xóa'), 
                                        'route'         => route('admin.post-categories.destroy', $postCategory->uuid ?? $postCategory->id), 
                                        'method'        => 'DELETE', 
                                        'color'         => 'red', 
                                        'confirm_title' => __('Xóa danh mục?'),
                                        'confirm_text'  => __('Thao tác này không thể hoàn tác.'),
                                        'confirm_btn'   => __('Xóa ngay')
                                    ]
                                ];
                            @endphp
                            <x-admin.row-actions :actions="$actions" />
                        </div>
                    </div>
                </td>
                @foreach($activeLanguages as $lang)
                    @php
                        $hasTrans = $postCategory->translations->contains('locale', $lang->code);
                    @endphp
                    <td class="py-3 px-2 text-center">
                        @if($hasTrans)
                            <a href="{{ route('admin.post-categories.edit', ['uuid' => $postCategory->uuid, 'lang' => $lang->code]) }}" class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-slate-100 dark:hover:bg-slate-700 text-blue-500 transition-colors" title="{{ __('Sửa bản dịch') }} {{ $lang->name }}">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </a>
                        @else
                            <a href="{{ route('admin.post-categories.edit', ['uuid' => $postCategory->uuid, 'lang' => $lang->code]) }}" class="inline-flex items-center justify-center w-6 h-6 rounded hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-400 hover:text-green-600 transition-colors" title="{{ __('Thêm bản dịch') }} {{ $lang->name }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </a>
                        @endif
                    </td>
                @endforeach
                <td class="py-3 px-6 text-center text-slate-600 dark:text-slate-400 text-sm">{{ $postCategory->display_order ?? 0 }}</td>
                <td class="py-3 px-6">
                    @if($postCategory->is_active ?? true)
                        <x-admin.badge label="{{ __('Hoạt động') }}" color="green" />
                    @else
                        <x-admin.badge label="{{ __('Đã ẩn') }}" color="red" />
                    @endif
                </td>
                <td class="py-3 px-6 text-slate-500 text-sm">{{ optional($postCategory->created_at)->format('d/m/Y H:i') ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-8 text-slate-500">{{ __('Không tìm thấy danh mục nào phù hợp.') }}</td>
            </tr>
        @endforelse
    </x-admin.table>
        </div>
    </div>
</div>
@endsection
