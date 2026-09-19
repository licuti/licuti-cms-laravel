@extends('layouts.admin')
@section('title', __('Quản lý Thẻ Tag'))

@section('content')
<div class="d-flex flex-column gap-4">
    <x-admin.page-header 
        title="{{ __('Danh sách Thẻ Tag') }}" 
        subtitle="{{ __('Quản lý từ khóa, thẻ phân loại bài viết và nội dung') }}" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Thẻ Tag')]
        ]">
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.tags.create') }}" variant="primary" class="flex-shrink-0" size="sm">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>{{ __('Thêm mới') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Thanh tìm kiếm --}}
    <div class="d-flex justify-content-end align-items-center gap-2">
        <form action="{{ route('admin.tags.index') }}" method="GET" class="d-flex align-items-center gap-2">
            <x-admin.input 
                type="text" 
                name="keyword" 
                value="{{ request('keyword') }}" 
                placeholder="{{ __('Tìm tên thẻ, slug...') }}" 
                size="sm" 
                style="min-width: 260px;" 
            />
            <x-admin.button type="submit" variant="primary" size="sm" class="flex-shrink-0">{{ __('Lọc') }}</x-admin.button>
            @if(request()->has('keyword'))
                <x-admin.button href="{{ route('admin.tags.index') }}" variant="outline-secondary" size="sm" class="flex-shrink-0">{{ __('Xóa lọc') }}</x-admin.button>
            @endif
        </form>
    </div>

    {{-- Bảng hiển thị danh sách Tags --}}
    <x-admin.table :paginator="$tags">
        <x-slot:head>
            <x-admin.table-th>{{ __('Tên thẻ tag') }}</x-admin.table-th>
            <x-admin.table-th align="center" width="160px">{{ __('Số bài viết') }}</x-admin.table-th>
            <x-admin.table-th align="center" width="100px">{{ __('Thứ tự') }}</x-admin.table-th>
            <x-admin.table-th width="140px">{{ __('Ngày tạo') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($tags as $tag)
            <tr class="group">
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary 
                        :title="$tag->name" 
                        :subtitle="'/' . $tag->slug" 
                        :actions="$tag->available_actions" 
                    />
                </td>
                <td class="py-3 px-3 text-center">
                    <span class="badge text-bg-light border text-body-secondary px-2 py-1">
                        {{ $tag->posts_count ?? 0 }} {{ __('bài viết') }}
                    </span>
                </td>
                <td class="py-3 px-3 text-center small text-body-secondary">
                    {{ $tag->display_order ?? 0 }}
                </td>
                <td class="py-3 px-3 small text-body-secondary">
                    {{ $tag->created_at?->format('d/m/Y') ?? '---' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center py-4 text-body-secondary">
                    {{ __('Không tìm thấy thẻ tag nào phù hợp.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
</div>
@endsection