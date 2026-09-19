@extends('layouts.admin')
@section('title', __('Quản lý Danh mục sản phẩm'))

@section('content')
<div class="d-flex flex-column gap-4">
    <x-admin.page-header
        title="{{ __('Danh sách Danh mục sản phẩm') }}"
        subtitle="{{ __('Quản lý cây danh mục catalog, thứ tự và trạng thái hiển thị') }}"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Danh mục sản phẩm')],
        ]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.categories.create') }}" variant="primary" class="flex-shrink-0" size="sm">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>{{ __('Thêm mới trang riêng') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row g-4 align-items-start">
        {{-- Cột Trái: Form Thêm nhanh --}}
        <div class="col-lg-4">
            @include('admin.categories._quick_form')
        </div>

        {{-- Cột Phải: Bảng Danh sách --}}
        <div class="col-lg-8">
            <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3 mb-3">
                {{-- Bulk Actions --}}
                <div class="d-flex align-items-center gap-2">
                    <x-admin.select id="bulk-action-select" class="w-auto fw-medium" size="sm">
                        <option value="">{{ __('Hành động hàng loạt...') }}</option>
                        <option value="delete">{{ __('Xóa đã chọn') }}</option>
                        <option value="status_1">{{ __('Chuyển trạng thái: Hoạt động') }}</option>
                        <option value="status_0">{{ __('Chuyển trạng thái: Đã ẩn') }}</option>
                    </x-admin.select>
                    <x-admin.button type="button" id="btn-apply-bulk" variant="secondary" class="flex-shrink-0" size="sm">{{ __('Áp dụng') }}</x-admin.button>
                </div>

                {{-- Search & Filter Form --}}
                <form action="{{ route('admin.categories.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-grow-1 justify-content-end">
                    <x-admin.input 
                        type="text" 
                        name="keyword" 
                        value="{{ request('keyword') }}" 
                        placeholder="{{ __('Tìm tên, slug danh mục...') }}" 
                        size="sm" 
                        style="min-width: 220px;" 
                    />
                    <x-admin.select name="status" class="w-auto" size="sm">
                        <option value="">{{ __('Tất cả trạng thái') }}</option>
                        <option value="1" @selected(request('status') === '1')>{{ __('Hoạt động') }}</option>
                        <option value="0" @selected(request('status') === '0')>{{ __('Đã ẩn') }}</option>
                    </x-admin.select>
                    <x-admin.button type="submit" variant="primary" size="sm" class="flex-shrink-0">{{ __('Lọc') }}</x-admin.button>
                    @if(request()->hasAny(['keyword', 'status']))
                        <x-admin.button href="{{ route('admin.categories.index') }}" variant="outline-secondary" size="sm" class="flex-shrink-0">{{ __('Xóa lọc') }}</x-admin.button>
                    @endif
                </form>
            </div>

            {{-- Hidden Bulk Action Form --}}
            <form action="{{ route('admin.categories.bulk') }}" method="POST" id="form-bulk-action" class="d-none">
                @csrf
                <input type="hidden" name="action" id="bulk-action-input">
            </form>

            <x-admin.table tbodyId="categories-table-body" :paginator="$categories ?? collect()">
                <x-slot:head>
                    <x-admin.table-th padding="text-center" align="center" width="40px">
                        <input type="checkbox" id="check-all" class="form-check-input">
                    </x-admin.table-th>
                    <x-admin.table-th>{{ __('Danh mục') }}</x-admin.table-th>
                    @foreach($activeLanguages as $lang)
                        <x-admin.table-th align="center" padding="text-center" width="50px" title="{{ $lang->native_name ?: $lang->name }}">
                            <span class="badge text-bg-light border text-uppercase small">{{ $lang->code }}</span>
                        </x-admin.table-th>
                    @endforeach
                    <x-admin.table-th align="center" width="80px">{{ __('Thứ tự') }}</x-admin.table-th>
                    <x-admin.table-th width="120px">{{ __('Trạng thái') }}</x-admin.table-th>
                    <x-admin.table-th width="120px">{{ __('Ngày tạo') }}</x-admin.table-th>
                </x-slot:head>

                @forelse($categories ?? [] as $category)
                    <tr class="group">
                        <td class="py-3 px-3 text-center">
                            <input type="checkbox" name="ids[]" value="{{ $category->uuid ?? $category->id }}" class="row-checkbox form-check-input">
                        </td>
                        <td class="py-3 px-3">
                            <x-admin.table-cell-primary 
                                :image="$category->image_url" 
                                :title="$category->tree_name ?? $category->translated_name" 
                                :subtitle="$category->translated_slug ? '/' . $category->translated_slug : null" 
                                :actions="$category->available_actions" 
                            />
                        </td>
                        @foreach($activeLanguages as $lang)
                            @php
                                $hasTrans = $category->translations->contains('locale', $lang->code);
                            @endphp
                            <td class="py-3 px-2 text-center">
                                @if($hasTrans)
                                    <a href="{{ route('admin.categories.edit', ['uuid' => $category->uuid, 'lang' => $lang->code]) }}" class="btn btn-sm btn-icon text-primary" title="{{ __('Sửa bản dịch') }} {{ $lang->name }}">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                @else
                                    <a href="{{ route('admin.categories.edit', ['uuid' => $category->uuid, 'lang' => $lang->code]) }}" class="btn btn-sm btn-icon text-body-tertiary" title="{{ __('Thêm bản dịch') }} {{ $lang->name }}">
                                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    </a>
                                @endif
                            </td>
                        @endforeach
                        <td class="py-3 px-3 text-center small text-body-secondary">
                            {{ $category->display_order ?? 0 }}
                        </td>
                        <td class="py-3 px-3">
                            @if($category->is_active ?? true)
                                <x-admin.badge label="{{ __('Hoạt động') }}" color="green" />
                            @else
                                <x-admin.badge label="{{ __('Đã ẩn') }}" color="gray" />
                            @endif
                        </td>
                        <td class="py-3 px-3 small text-body-secondary">
                            {{ optional($category->created_at)->format('d/m/Y') ?? '---' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 5 + count($activeLanguages) }}" class="text-center py-4 text-body-secondary">
                            {{ __('Không tìm thấy danh mục nào phù hợp.') }}
                        </td>
                    </tr>
                @endforelse
            </x-admin.table>
        </div>
    </div>
</div>
@endsection
