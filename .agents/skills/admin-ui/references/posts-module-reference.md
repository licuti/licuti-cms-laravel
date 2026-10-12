# Module Posts — Golden Standard Reference

> Đây là code thực tế đang chạy trong module Posts, được dùng làm chuẩn tham chiếu
> cho tất cả các module admin khác. Khi tạo module mới, hãy sao chép cấu trúc này
> và thay đổi nội dung phù hợp.

---

## Index — Trang Danh sách Bài viết

**File**: `resources/views/admin/posts/index.blade.php`

```blade
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
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Thêm mới</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Tabs lọc trạng thái --}}
    <x-admin.filter-tabs :items="$tabs" :current="$tab" route="admin.posts.index" />

    {{-- Thanh Bulk Action & Filter --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
        <div class="d-flex align-items-center gap-2">
            <x-admin.select id="bulk-action-select" class="w-auto fw-medium" size="sm">
                <option value="">{{ __('Hành động hàng loạt...') }}</option>
                @foreach($bulkActions as $actionKey => $actionLabel)
                    <option value="{{ $actionKey }}">{{ $actionLabel }}</option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="button" id="btn-apply-bulk" variant="primary" class="flex-shrink-0" size="sm">
                {{ __('Áp dụng') }}
            </x-admin.button>
        </div>

        <form action="{{ route('admin.posts.index') }}" method="GET" id="form-filter-posts"
              class="d-flex align-items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}"
                placeholder="Tìm tiêu đề, slug..." size="sm" style="min-width:280px;" />
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

    {{-- Bảng dữ liệu --}}
    <x-admin.table :paginator="$posts">
        <x-slot:head>
            <x-admin.table-th align="center">
                <input type="checkbox" id="check-all" class="form-check-input">
            </x-admin.table-th>
            <x-admin.table-th>Tiêu đề</x-admin.table-th>
            <x-admin.table-th>Danh mục</x-admin.table-th>
            <x-admin.table-th>Trạng thái</x-admin.table-th>
            <x-admin.table-th>Ngày tạo</x-admin.table-th>
        </x-slot:head>

        @forelse($posts as $post)
            <tr class="group">
                <td class="py-3 px-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $post->id }}" class="row-checkbox form-check-input">
                </td>
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary
                        :image="$image"
                        :title="$title"
                        :subtitle="'/' . $slug"
                        :actions="$actions" />
                </td>
                <td class="py-3 px-3 small text-body-secondary">{{ $post->category_names }}</td>
                <td class="py-3 px-3">
                    <x-admin.badge :label="$post->status_label" :color="$post->status_color" />
                </td>
                <td class="py-3 px-3 small text-body-secondary">
                    {{ $post->created_at?->format('d/m/Y') ?? '---' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center py-5 text-body-secondary">
                    {{ __('Không có dữ liệu.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection
```

### Điểm chuẩn cần tuân thủ:

1. **Page Header**: Có `title`, `subtitle`, `breadcrumbs`, slot `actions` chứa nút "Thêm mới"
2. **Filter Tabs**: Dùng `<x-admin.filter-tabs>` cho trạng thái
3. **Toolbar**: `d-flex justify-content-between align-items-center gap-3 mb-2`
4. **Bulk Action**: `variant="primary"` cho nút "Áp dụng"
5. **Table**: Dùng `<x-admin.table>` với `paginator`, `<x-admin.table-th>`, `<x-admin.table-cell-primary>`
6. **Row Actions**: Truyền qua prop `actions` của `table-cell-primary`
7. **Badge**: Dùng `<x-admin.badge>` cho trạng thái
8. **Padding ô bảng**: `py-3 px-3` thống nhất
9. **Text phụ**: `small text-body-secondary`

---

## Form — Trang Soạn thảo Bài viết (Create/Edit chung)

**File**: `resources/views/admin/posts/form.blade.php`

Posts dùng chung 1 file cho cả Create và Edit. Controller truyền `$post` (null khi Create)
và `$isEdit = !is_null($post)`.

### Cấu trúc 2 cột

```blade
@extends('layouts.admin')
@section('title', $isEdit ? 'Sửa bài viết' : 'Thêm bài viết mới')

@section('content')
    <x-admin.page-header
        :title="$isEdit ? 'Sửa bài viết' : 'Thêm bài viết mới'"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'],
            ['label' => 'Bài viết', 'url' => route('admin.posts.index')],
            ['label' => $isEdit ? 'Sửa' : 'Thêm mới']
        ]" />

    <form action="{{ $isEdit ? route('admin.posts.update', $post->uuid) : route('admin.posts.store') }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="row g-4 align-items-start">

            {{-- ═══ CỘT CHÍNH (9/12) ═══ --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">

                {{-- Tabs ngôn ngữ (client-side) --}}
                <x-admin.lang-tabs :active-languages="$activeLanguages" :default-locale="$defaultLocale" />

                @foreach($activeLanguages as $lang)
                    @php $code = $lang->code; @endphp
                    <div class="lang-panel {{ $code !== $defaultLocale ? 'd-none' : '' }}"
                         data-lang="{{ $code }}">

                        {{-- Card nội dung chính --}}
                        <x-admin.card>
                            <div class="d-flex flex-column gap-3">
                                <x-admin.form-group label="Tiêu đề" name="translations.{{ $code }}.name" required>
                                    <x-admin.input name="translations[{{ $code }}][name]"
                                        :value="old('translations.'.$code.'.name', $post?->translate($code)?->name)"
                                        class="seo-source-name" required />
                                </x-admin.form-group>

                                <x-admin.form-group label="Slug" name="translations.{{ $code }}.slug">
                                    <x-admin.input name="translations[{{ $code }}][slug]"
                                        :value="old('translations.'.$code.'.slug', $post?->translate($code)?->slug)"
                                        class="seo-source-slug" />
                                </x-admin.form-group>

                                <x-admin.form-group label="Nội dung" name="translations.{{ $code }}.content">
                                    <x-admin.textarea name="translations[{{ $code }}][content]"
                                        class="tinymce-editor" rows="20"
                                        :value="old('translations.'.$code.'.content', $post?->translate($code)?->content)" />
                                </x-admin.form-group>

                                <x-admin.form-group label="Tóm tắt" name="translations.{{ $code }}.excerpt">
                                    <x-admin.textarea name="translations[{{ $code }}][excerpt]" rows="3"
                                        class="seo-source-excerpt"
                                        :value="old('translations.'.$code.'.excerpt', $post?->translate($code)?->excerpt)" />
                                </x-admin.form-group>
                            </div>
                        </x-admin.card>

                        {{-- Card SEO --}}
                        <x-admin.card title="Tối ưu hóa Công cụ Tìm kiếm (SEO)">
                            <x-admin.seo-meta :lang="$lang"
                                :seo="$post?->seoForLocale($code)"
                                :show-header="false" />
                        </x-admin.card>

                    </div>
                @endforeach
            </div>

            {{-- ═══ SIDEBAR (3/12) ═══ --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- Hộp Xuất bản --}}
                <x-admin.publish-box
                    :statuses="$statuses"
                    :status="old('status', $post?->status)"
                    :published-at="old('published_at', $post?->published_at)"
                    :show-featured="true"
                    :featured="old('is_featured', $post?->is_featured)"
                    :index-route="route('admin.posts.index')" />

                {{-- Chuyên mục --}}
                <x-admin.card title="Chuyên mục">
                    <x-admin.tree-checkbox name="category_ids"
                        :options="$categories"
                        :selected="old('category_ids', $post?->categories->pluck('id')->toArray() ?? [])" />
                </x-admin.card>

                {{-- Ảnh đại diện --}}
                <x-admin.card title="Ảnh đại diện">
                    <x-admin.image-upload name="image" shape="wide"
                        :current="$post?->image_url"
                        :current-uuid="$post?->image_uuid" />
                </x-admin.card>

                {{-- Thông tin bài viết (chỉ hiện khi Edit) --}}
                @if($isEdit)
                    <x-admin.card title="Thông tin bài viết">
                        <div class="d-flex flex-column gap-3">
                            <x-admin.form-group label="Tác giả" name="author_id">
                                <x-admin.select name="author_id">
                                    @foreach($authors as $author)
                                        <option value="{{ $author->id }}"
                                            {{ $post->author_id == $author->id ? 'selected' : '' }}>
                                            {{ $author->name }}
                                        </option>
                                    @endforeach
                                </x-admin.select>
                            </x-admin.form-group>
                            <div class="small text-body-secondary">
                                Lượt xem: {{ number_format($post->views ?? 0) }}
                            </div>
                        </div>
                    </x-admin.card>
                @endif
            </div>

        </div>
    </form>

    {{-- Scripts --}}
    <x-admin.scripts.auto-slug :is-edit="$isEdit" />
    <x-admin.scripts.tinymce />
@endsection
```

### Điểm chuẩn cần tuân thủ:

1. **1 file dùng chung**: Create + Edit, phân biệt qua `$isEdit`
2. **Grid 2 cột**: `row g-4 align-items-start` → `col-md-8 col-lg-9` + `col-md-4 col-lg-3`
3. **Đa ngôn ngữ**: `<x-admin.lang-tabs>` (client-side), mỗi ngôn ngữ trong `.lang-panel`
4. **Nội dung card**: KHÔNG lồng thêm `.card-body`, dùng `d-flex flex-column gap-3` cho các field
5. **Sidebar**: `<x-admin.publish-box>` đứng đầu → Cards phụ trợ
6. **SEO**: `<x-admin.seo-meta>` trong card riêng, gọi đúng `:lang` và `:seo`
7. **Scripts cuối file**: `auto-slug` + `tinymce` trước `@endsection`
8. **Form action**: Dùng `route()` helper + `$item->uuid` (không expose integer id)

---

## Tóm tắt: Mapping Component → Vị trí trong Page

### Trang Index

| Vị trí | Component |
|---|---|
| Header | `x-admin.page-header` (slot `actions` → `x-admin.button`) |
| Tabs | `x-admin.filter-tabs` |
| Toolbar trái | `x-admin.select` + `x-admin.button` |
| Toolbar phải | `x-admin.input` + `x-admin.select` + `x-admin.button` |
| Bảng | `x-admin.table` (slot `head` → `x-admin.table-th`) |
| Ô chính | `x-admin.table-cell-primary` (nhúng `x-admin.row-actions`) |
| Trạng thái | `x-admin.badge` |

### Trang Form

| Vị trí | Component |
|---|---|
| Header | `x-admin.page-header` |
| Tabs ngôn ngữ | `x-admin.lang-tabs` |
| Input fields | `x-admin.form-group` → `x-admin.input` / `x-admin.textarea` / `x-admin.select` |
| SEO | `x-admin.seo-meta` |
| Sidebar: Xuất bản | `x-admin.publish-box` |
| Sidebar: Chuyên mục | `x-admin.tree-checkbox` |
| Sidebar: Ảnh | `x-admin.image-upload` |
| Scripts | `x-admin.scripts.auto-slug` + `x-admin.scripts.tinymce` |
