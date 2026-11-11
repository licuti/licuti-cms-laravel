@extends('layouts.admin')
@section('title', 'Quản lý Bài viết')

@section('content')
    <x-admin.page-header 
        title="Danh sách Bài viết" 
        subtitle="" 
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
            <x-admin.select id="bulk-action-select" class="w-auto fw-medium">
                <option value="">{{ __('Hành động hàng loạt...') }}</option>
                @foreach($bulkActions as $actionKey => $actionLabel)
                    <option value="{{ $actionKey }}">{{ $actionLabel }}</option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="button" id="btn-apply-bulk" variant="primary" class="flex-shrink-0">{{ __('Áp dụng') }}</x-admin.button>
        </div>

        {{-- Search & Filter Form --}}
        <form action="{{ route('admin.posts.index') }}" method="GET" id="form-filter-posts" class="d-flex align-items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm tiêu đề, slug..." style="min-width:280px;">
            </x-admin.input>
            <x-admin.select name="category" class="w-auto">
                <option value="">Tất cả danh mục</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="submit" variant="primary">Lọc</x-admin.button>
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
            <x-admin.table-th align="center">
                <input type="checkbox" id="check-all" class="form-check-input">
            </x-admin.table-th>
            <x-admin.table-th>{{ __('Ảnh') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Tiêu đề') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Danh mục') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Ngôn ngữ') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Trạng thái') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Ngày tạo') }}</x-admin.table-th>
        </x-slot:head>
        @forelse($posts as $post)
            @php
                $title = $post->translate(app()->getLocale())?->title ?? ($post->translations->first()?->title ?? '---');
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
            <tr>
                <td class="text-center">
                    <input type="checkbox" name="ids[]" value="{{ $post->id }}" class="row-checkbox form-check-input">
                </td>
                <td>
                    @if($image)
                        <img src="{{ $image }}" alt="" class="rounded border object-fit-cover" style="width:2.5rem;height:2.5rem;">
                    @else
                        <span class="rounded border bg-body-tertiary text-body-secondary d-inline-flex align-items-center justify-content-center" style="width:2.5rem;height:2.5rem;">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                    @endif
                </td>
                <td>
                    <div class="fw-semibold">{{ $title }}</div>
                    <x-admin.row-actions :actions="$actions" />
                </td>
                <td class="text-body-secondary">{{ $post->category_names }}</td>
                <td>
                    <x-admin.language-flags
                        :locales="$post->translations->pluck('locale')"
                        route="admin.posts.edit"
                        :route-params="['uuid' => $post->uuid]"
                    />
                </td>
                <td>
                    <x-admin.badge :label="$post->status_label" :color="$post->status_color" />
                </td>
                <td class="text-body-secondary">
                    {{ $post->created_at?->format('d/m/Y') ?? '---' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-4">
                    {{ __('Không tìm thấy bài viết nào phù hợp.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection