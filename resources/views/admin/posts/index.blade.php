@extends('layouts.admin')
@section('title', 'Quản lý Bài viết')

@section('content')
    <x-admin.page-header 
        title="Danh sách Bài viết" 
        subtitle="Quản lý bài viết blog, tin tức và nội dung CMS" 
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'],
            ['label' => 'Bài viết']
        ]">
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.posts.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Thêm mới</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Tab Navigation --}}
    <x-admin.filter-tabs :items="$tabs" :current="$tab" route="admin.posts.index" />

    {{-- Bulk Actions + Search/Filter Bar --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
        {{-- Bulk Actions --}}
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
        <form action="{{ route('admin.posts.index') }}" method="GET" id="form-filter-posts" class="d-flex align-items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm tiêu đề, slug..." size="sm" style="min-width:280px;">
            </x-admin.input>
            <x-admin.select name="category" class="w-auto" size="sm">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="submit" variant="primary" size="sm">Lọc</x-admin.button>
        </form>
    </div>

    {{-- Hidden Bulk Action Form --}}
    <form id="form-bulk-action" action="{{ route('admin.posts.bulk') }}" method="POST">
        @csrf
        <input type="hidden" name="bulk_module" value="posts">
        <input type="hidden" name="action" id="bulk-action-input" value="">
    </form>

    <x-admin.table :paginator="$posts">
        <x-slot:head>
            <x-admin.table-th padding="text-center" align="center">
                <input type="checkbox" id="check-all" class="form-check-input">
            </x-admin.table-th>
            <x-admin.table-th>Tiêu đề</x-admin.table-th>
            <x-admin.table-th>Danh mục</x-admin.table-th>
            <x-admin.table-th>Trạng thái</x-admin.table-th>
            <x-admin.table-th>Ngày tạo</x-admin.table-th>
        </x-slot:head>
        @forelse($posts as $post)
            @php
                $title = $post->translate(app()->getLocale())?->title ?? ($post->translations->first()?->title ?? '---');
                $slug = $post->translate(app()->getLocale())?->slug ?? ($post->translations->first()?->slug ?? '');
                $actions = [
                    ['label' => 'Sửa', 'route' => route('admin.posts.edit', $post->uuid), 'color' => 'blue'],
                    [
                        'label'         => 'Xóa',
                        'route'         => route('admin.posts.destroy', $post->uuid),
                        'method'        => 'DELETE',
                        'color'         => 'red',
                        'confirm_title' => 'Xóa bài viết?',
                        'confirm_text'  => 'Bạn có chắc chắn muốn xóa bài viết này?',
                        'confirm_btn'   => 'Xóa ngay'
                    ],
                ];
                $image = $post->image_url ?? ($post->image && !preg_match('/^[0-9a-f-]{36}$/i', $post->image) ? asset('storage/' . $post->image) : null);
            @endphp
            <tr class="group">
                <td class="py-3 px-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $post->id }}" class="row-checkbox form-check-input">
                </td>
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary :image="$image" :title="$title" :subtitle="'/' . $slug" :actions="$actions" />
                </td>
                <td class="py-3 px-3 small text-body-secondary">
                    {{ $post->category_names }}
                </td>
                <td class="py-3 px-3">
                    <x-admin.badge :label="$post->status_label" :color="$post->status_color" />
                </td>
                <td class="py-3 px-3 small text-body-secondary">
                    {{ $post->created_at?->format('d/m/Y') ?? '---' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-4">
                    Không tìm thấy bài viết nào phù hợp.
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection