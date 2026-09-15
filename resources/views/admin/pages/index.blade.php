@extends('layouts.admin')
@section('title', __('Quản lý Trang tĩnh'))

@section('content')
@php
    $locale = app()->getLocale();
@endphp
<div class="space-y-4">
    <x-admin.page-header
        title="{{ __('Danh sách Trang tĩnh') }}"
        subtitle="{{ __('Quản lý các trang tĩnh của website') }}"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Trang tĩnh')],
        ]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.pages.create') }}" variant="primary" class="shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>{{ __('Thêm Trang tĩnh mới') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="flex flex-col md:flex-row md:items-center justify-end gap-4">
        <form action="{{ route('admin.pages.index') }}" method="GET"
              class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 flex-1 md:max-w-xl justify-end">
            <div class="flex-1 min-w-[200px]">
                <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}"
                               placeholder="{{ __('Tìm tiêu đề, slug...') }}" size="sm">
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
                <x-admin.button href="{{ route('admin.pages.index') }}" variant="outline" size="sm" class="shrink-0">{{ __('Xóa') }}</x-admin.button>
            @endif
        </form>
    </div>

    <x-admin.table :paginator="$pages">
        <x-slot:head>
            <x-admin.table-th>{{ __('Tiêu đề / Thao tác') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Slug') }}</x-admin.table-th>
            <x-admin.table-th align="center">{{ __('Thứ tự') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Trạng thái') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Ngày tạo') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($pages as $page)
            @php
                $trans = $page->translations->firstWhere('locale', $locale)
                    ?? $page->translations->first();
                $title = $trans?->title ?? $page->title ?? '-';
                $slug = $trans?->slug ?? $page->slug ?? '';
                $actions = [
                    [
                        'label' => __('Sửa'),
                        'route' => route('admin.pages.edit', $page->uuid),
                        'method' => 'GET',
                        'color' => 'blue',
                    ],
                    [
                        'label' => __('Xóa'),
                        'route' => route('admin.pages.destroy', $page->uuid),
                        'method' => 'DELETE',
                        'color' => 'red',
                        'confirm_title' => __('Xóa trang tĩnh?'),
                        'confirm_text'  => __('Thao tác này không thể hoàn tác.'),
                        'confirm_btn'   => __('Xóa ngay')
                    ],
                ];
            @endphp
            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                <td class="py-3 px-6">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-blue-600 to-cyan-500 flex items-center justify-center text-white shrink-0 shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-slate-100 text-sm">{{ $title }}</p>
                            @if($slug)<p class="text-sm text-slate-500">{{ $slug }}</p>@endif
                            <x-admin.row-actions :actions="$actions" />
                        </div>
                    </div>
                </td>
                <td class="py-3 px-6 text-slate-600 dark:text-slate-400 text-sm">{{ $slug }}</td>
                <td class="py-3 px-6 text-center text-slate-600 dark:text-slate-400 text-sm">{{ $page->display_order ?? 0 }}</td>
                <td class="py-3 px-6">
                    @if($page->is_active)
                        <x-admin.badge label="{{ __('Hoạt động') }}" color="green" />
                    @else
                        <x-admin.badge label="{{ __('Đã ẩn') }}" color="red" />
                    @endif
                </td>
                <td class="py-3 px-6 text-slate-500 text-sm">{{ $page->created_at?->format('d/m/Y') ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-8 text-slate-500">{{ __('Không tìm thấy trang tĩnh nào phù hợp.') }}</td>
            </tr>
        @endforelse
    </x-admin.table>
</div>
@endsection
