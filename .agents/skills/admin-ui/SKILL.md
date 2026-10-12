---
name: admin-ui
description: >-
  Chuẩn UI/UX bắt buộc cho toàn bộ Admin Panel của Licuti CMS (Bootstrap 5.3 + SCSS).
  Kích hoạt khi tạo, sửa hoặc review bất kỳ file Blade view, Blade component,
  SCSS hoặc JS nào trong khu vực Admin. Bao gồm component catalog đầy đủ,
  page template chuẩn (trang danh sách, trang form), layout grid, và ví dụ
  thực tế từ module Posts. Cũng kích hoạt khi user hỏi cách dùng
  x-admin.card, x-admin.button, x-admin.table, x-admin.publish-box, hoặc
  bất kỳ component admin nào khác.
---

# Tiêu chuẩn UI Admin — Licuti CMS

> Tài liệu này là **nguồn sự thật duy nhất (Single Source of Truth)** cho mọi
> quyết định UI trong Admin Panel. Tuân thủ 100% trước khi viết bất kỳ dòng
> Blade/SCSS/JS nào.
>
> **Golden standard**: Module Posts (`resources/views/admin/posts/`).
> Xem code thực tế đầy đủ tại [`references/posts-module-reference.md`](./references/posts-module-reference.md).

---

## 1. Hai Kiểu trang Admin (Page Archetypes)

Mọi trang admin đều thuộc **1 trong 2 kiểu**:

### Kiểu A — Trang Danh sách (Index Page)

Cấu trúc từ trên xuống:

```
1. <x-admin.page-header>        → Tiêu đề + Breadcrumbs + Nút "Thêm mới"
2. <x-admin.filter-tabs>        → Tabs lọc trạng thái (nếu có)
3. Toolbar (div flex)            → Bulk Actions bên trái + Search/Filter bên phải
4. <x-admin.table>              → Bảng dữ liệu + Phân trang tự động
```

### Kiểu B — Trang Form (Create/Edit)

Dùng chung 1 file `form.blade.php` cho cả Create và Edit (phân biệt qua `$isEdit`).
Cấu trúc 2 cột:

```
<form>
  <div class="row g-4 align-items-start">

    <!-- CỘT CHÍNH: col-md-8 col-lg-9 -->
    <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">
      <x-admin.lang-tabs />          ← Tabs ngôn ngữ (nếu đa ngôn ngữ)
      @foreach lang-panels:
        <x-admin.card>                ← Nội dung chính (title, slug, content...)
        <x-admin.card title="SEO">   ← SEO Meta
      @endforeach
    </div>

    <!-- SIDEBAR: col-md-4 col-lg-3 -->
    <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">
      <x-admin.publish-box />         ← Xuất bản: Status + Date + Featured + Buttons
      <x-admin.card>                  ← Cards phụ trợ (chuyên mục, ảnh đại diện...)
    </div>

  </div>
</form>
<x-admin.scripts.auto-slug />        ← Scripts cuối file
<x-admin.scripts.tinymce />
```

---

## 2. Component Catalog

### 2.1 Layout & Navigation

| Component | Props chính | Mô tả |
|---|---|---|
| `x-admin.page-header` | `title`, `subtitle`, `breadcrumbs` | Header trang + Breadcrumbs. Slot `actions` cho nút hành động |
| `x-admin.filter-tabs` | `items`, `current`, `route` | Tabs lọc trạng thái (Tất cả / Đã xuất bản / Nháp...) |
| `x-admin.lang-tabs` | `activeLanguages`, `defaultLocale` | Tabs chuyển ngôn ngữ **client-side** (toggle `.lang-panel` + class `d-none`) |

### 2.2 Container

| Component | Props chính | Mô tả |
|---|---|---|
| `x-admin.card` | `title` | Card Bootstrap. Slot `action` cho nút header phải. **Đã tự bọc `.card-body`** — KHÔNG lồng thêm |
| `x-admin.modal` | `id`, `title`, `maxWidth` | Modal Bootstrap 5. Slot `footer`. JS: `openModal(id)`, `closeModal(id)` |

### 2.3 Form Controls

| Component | Props chính | Mô tả |
|---|---|---|
| `x-admin.form-group` | `label`, `name`, `description`, `required` | Wrapper: label (kèm `*` đỏ nếu required) + slot + error message tự động |
| `x-admin.input` | `type`, `size` (`sm`/`md`), `disabled`, `required` | Input text. Slots `prefix`/`suffix` (auto `.input-group`). Auto `is-invalid` |
| `x-admin.select` | `size` (`sm`/`md`), `disabled`, `required` | Select dropdown. Auto `is-invalid` khi có lỗi validation |
| `x-admin.textarea` | `size`, `rows` (mặc định 4), `disabled`, `required` | Textarea. Tự strip bracket name cho error check |
| `x-admin.toggle` | `name`, `label`, `checked`, `size`, `color`, `action` | Switch Bootstrap `.form-check.form-switch`. Có hidden input `0` dự phòng |
| `x-admin.datetime-field` | *(xem component)* | Input datetime picker |
| `x-admin.image-upload` | `name`, `current`, `currentUuid`, `shape` (`wide`/`square`/`circle`), `label` | Upload ảnh qua modal media picker toàn cục |
| `x-admin.tree-checkbox` | `name`, `options`, `selected` | Checkbox dạng cây (chuyên mục cha-con) |

### 2.4 Data Display

| Component | Props chính | Mô tả |
|---|---|---|
| `x-admin.table` | `paginator`, `paginationId`, `tbodyId` | Bảng data bọc trong card. Slot `head`. Auto pagination footer |
| `x-admin.table-th` | `align` (`left`/`center`/`right`), `padding` | Header cell `<th>` chuẩn hóa |
| `x-admin.table-cell-primary` | `image`, `title`, `subtitle`, `actions`, `initial`, `imageShape` | Ô chính: ảnh/placeholder gradient + title + subtitle + row actions |
| `x-admin.row-actions` | `actions` (mảng object) | Nút Sửa/Xóa ẩn, hiện khi hover hàng. Class `.row-hover-actions` |
| `x-admin.badge` | `label`, `color` | Badge trạng thái. Dùng Bootstrap `text-bg-{color}` |

### 2.5 Specialized

| Component | Props chính | Mô tả |
|---|---|---|
| `x-admin.publish-box` | `statuses`, `status`, `publishedAt`, `showPublishedAt`, `showFeatured`, `featured`, `featuredLabel`, `indexRoute`, `saveLabel`, `defaultNow` | Sidebar "Xuất bản": Select trạng thái + Date picker + Toggle nổi bật + Cụm nút (Quay lại / Lưu / Lưu & Sửa với `name="submit_action"`) |
| `x-admin.seo-meta` | `lang`, `seo`, `slug`, `showHeader` | Form SEO đầy đủ: Meta (title/desc + đếm ký tự), OG (title/desc/image), Nâng cao (canonical/robots/schema) + Live Preview Google & Facebook |
| `x-admin.button` | `variant`, `size` (mặc định `sm`), `type`, `href` | Nếu có `href` → `<a>`, không thì `<button>`. Auto class: `btn btn-{variant} btn-{size} d-inline-flex align-items-center gap-1` |

**Biến thể `x-admin.button`:**

| variant | Class Bootstrap |
|---|---|
| `primary` | `btn-primary` |
| `secondary` | `btn-light` |
| `danger` | `btn-danger` |
| `outline-primary` | `btn-outline-primary` |
| `outline-secondary` | `btn-outline-secondary` |

### 2.6 Scripts (đặt cuối file Blade, trước `@endsection`)

| Component | Props | Mô tả |
|---|---|---|
| `x-admin.scripts.auto-slug` | `isEdit` (bool) | Tự sinh slug từ tiêu đề, đồng bộ sang SEO meta title/description |
| `x-admin.scripts.tinymce` | *(không)* | Khởi tạo TinyMCE trên `.tinymce-editor`, auto sync theme dark/light |

### 2.7 Component DEPRECATED — KHÔNG ĐƯỢC DÙNG

| Component | Lý do | Thay thế bằng |
|---|---|---|
| `x-admin.scripts.seo-preview` | Logic đã gộp vào `x-admin.seo-meta` | `<x-admin.seo-meta>` |
| `x-admin.language-switcher-widget` | Server-side reload kém UX | `<x-admin.lang-tabs>` (client-side) |

---

## 3. Quy tắc Layout & Spacing

### Grid

| Layout | Cú pháp |
|---|---|
| Form 2 cột | `row g-4 align-items-start` → `col-md-8 col-lg-9` + `col-md-4 col-lg-3` |
| Stat cards / Gallery | `row g-3 row-cols-1 row-cols-sm-2 row-cols-lg-4` |

### Spacing

| Mục đích | Class |
|---|---|
| Nhóm dọc các card/block | `d-flex flex-column gap-4` |
| Nhóm dọc các field trong card | `d-flex flex-column gap-3` |
| Nhóm ngang (buttons, filters) | `d-flex align-items-center gap-2` |
| Toolbar trên bảng | `d-flex justify-content-between align-items-center gap-3 mb-2` |
| Padding ô bảng | `py-3 px-3` |

### Dark Mode

- Hoàn toàn qua `data-bs-theme="dark"` (Bootstrap 5.3 native)
- Dùng class ngữ nghĩa: `bg-body`, `text-body`, `text-body-secondary`, `bg-body-tertiary`
- **CẤM**: mọi tiền tố `dark:*` (Tailwind)

---

## 4. Template Chuẩn — Trang Danh sách (Index)

> Tham chiếu thực tế: [`references/posts-module-reference.md` § Index](./references/posts-module-reference.md)

```blade
@extends('layouts.admin')
@section('title', 'Quản lý [Module]')

@section('content')
    {{-- 1. Page Header --}}
    <x-admin.page-header
        title="Danh sách [Module]"
        subtitle="Mô tả ngắn module"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'],
            ['label' => '[Module]']
        ]">
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.[module].create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Thêm mới</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- 2. Filter Tabs (nếu có trạng thái) --}}
    <x-admin.filter-tabs :items="$tabs" :current="$tab" route="admin.[module].index" />

    {{-- 3. Toolbar: Bulk Actions (trái) + Search/Filter (phải) --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
        <div class="d-flex align-items-center gap-2">
            <x-admin.select id="bulk-action-select" class="w-auto fw-medium" size="sm">
                <option value="">{{ __('Hành động hàng loạt...') }}</option>
                @foreach($bulkActions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </x-admin.select>
            <x-admin.button type="button" id="btn-apply-bulk" variant="primary" size="sm">
                {{ __('Áp dụng') }}
            </x-admin.button>
        </div>

        <form action="{{ route('admin.[module].index') }}" method="GET" class="d-flex align-items-center gap-2">
            <x-admin.input type="text" name="keyword" :value="request('keyword')"
                placeholder="Tìm kiếm..." size="sm" style="min-width:280px;" />
            <x-admin.button type="submit" variant="primary" size="sm">{{ __('Lọc') }}</x-admin.button>
        </form>
    </div>

    {{-- 4. Data Table --}}
    <x-admin.table :paginator="$items">
        <x-slot:head>
            <x-admin.table-th align="center">
                <input type="checkbox" id="check-all" class="form-check-input">
            </x-admin.table-th>
            <x-admin.table-th>{{ __('Tên') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Trạng thái') }}</x-admin.table-th>
            <x-admin.table-th>{{ __('Ngày tạo') }}</x-admin.table-th>
        </x-slot:head>

        @forelse($items as $item)
            <tr>
                <td class="py-3 px-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="row-checkbox form-check-input">
                </td>
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary
                        :image="$item->thumbnail_url"
                        :title="$item->name"
                        :subtitle="'/' . $item->slug"
                        :actions="[
                            ['label' => 'Sửa', 'route' => route('admin.[module].edit', $item->uuid), 'color' => 'primary'],
                            ['label' => 'Xóa', 'route' => route('admin.[module].destroy', $item->uuid), 'method' => 'DELETE', 'color' => 'danger',
                             'confirm_title' => 'Xóa mục này?', 'confirm_text' => 'Không thể hoàn tác!']
                        ]" />
                </td>
                <td class="py-3 px-3">
                    <x-admin.badge :label="$item->status_label" :color="$item->status_color" />
                </td>
                <td class="py-3 px-3 small text-body-secondary">
                    {{ $item->created_at?->format('d/m/Y') ?? '---' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center py-5 text-body-secondary">
                    {{ __('Không có dữ liệu.') }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection
```

---

## 5. Template Chuẩn — Trang Form (Create/Edit)

> Tham chiếu thực tế: [`references/posts-module-reference.md` § Form](./references/posts-module-reference.md)
>
> **Quy ước**: Dùng chung 1 file `form.blade.php` cho cả Create và Edit.
> Phân biệt qua biến `$isEdit = !is_null($item)`.

```blade
@extends('layouts.admin')
@section('title', $isEdit ? 'Sửa [Module]' : 'Thêm [Module]')

@section('content')
    <x-admin.page-header
        :title="$isEdit ? 'Sửa [Module]' : 'Thêm [Module] mới'"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'],
            ['label' => '[Module]', 'url' => route('admin.[module].index')],
            ['label' => $isEdit ? 'Sửa' : 'Thêm mới']
        ]" />

    <form action="{{ $isEdit ? route('admin.[module].update', $item->uuid) : route('admin.[module].store') }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="row g-4 align-items-start">

            {{-- ═══════════════════════════════════════════ --}}
            {{-- CỘT CHÍNH (9/12)                           --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">

                {{-- Tabs ngôn ngữ --}}
                <x-admin.lang-tabs :active-languages="$activeLanguages" :default-locale="$defaultLocale" />

                {{-- Nội dung theo từng ngôn ngữ --}}
                @foreach($activeLanguages as $lang)
                    @php $code = $lang->code; @endphp
                    <div class="lang-panel {{ $code !== $defaultLocale ? 'd-none' : '' }}"
                         data-lang="{{ $code }}">

                        <x-admin.card>
                            <div class="d-flex flex-column gap-3">
                                <x-admin.form-group label="Tiêu đề" name="translations.{{ $code }}.name" required>
                                    <x-admin.input name="translations[{{ $code }}][name]"
                                        :value="old('translations.'.$code.'.name', $item?->translate($code)?->name)"
                                        class="seo-source-name" required />
                                </x-admin.form-group>

                                <x-admin.form-group label="Slug" name="translations.{{ $code }}.slug">
                                    <x-admin.input name="translations[{{ $code }}][slug]"
                                        :value="old('translations.'.$code.'.slug', $item?->translate($code)?->slug)"
                                        class="seo-source-slug" />
                                </x-admin.form-group>

                                {{-- Thêm field nội dung / mô tả tùy module --}}
                            </div>
                        </x-admin.card>

                        {{-- SEO --}}
                        <x-admin.card title="Tối ưu hóa Công cụ Tìm kiếm (SEO)">
                            <x-admin.seo-meta :lang="$lang"
                                :seo="$item?->seoForLocale($code)"
                                :show-header="false" />
                        </x-admin.card>

                    </div>
                @endforeach
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- SIDEBAR (3/12)                              --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- Hộp Xuất bản --}}
                <x-admin.publish-box
                    :statuses="$statuses"
                    :status="old('status', $item?->status)"
                    :published-at="old('published_at', $item?->published_at)"
                    :index-route="route('admin.[module].index')" />

                {{-- Ảnh đại diện --}}
                <x-admin.card title="Ảnh đại diện">
                    <x-admin.image-upload name="image" shape="wide"
                        :current="$item?->image_url"
                        :current-uuid="$item?->image_uuid" />
                </x-admin.card>

                {{-- Cards phụ trợ khác tùy module --}}
            </div>

        </div>
    </form>

    {{-- Scripts cuối file --}}
    <x-admin.scripts.auto-slug :is-edit="$isEdit ?? false" />
    <x-admin.scripts.tinymce />
@endsection
```

---

## 6. Bảng Quy tắc CẤM (Quick Reference)

| ❌ CẤM | ✅ THAY THẾ BẰNG |
|---|---|
| `space-y-*`, `space-x-*` | `d-flex flex-column gap-3` hoặc `gap-4` |
| `grid-cols-*`, `w-64`, `p-4` (Tailwind) | `row col-*`, `p-3`, `px-3` (Bootstrap) |
| `dark:bg-*`, `dark:text-*` | `bg-body`, `text-body`, `text-body-secondary` |
| `gap-y-*` | `gap-3` hoặc `gap-4` (Bootstrap gap) |
| `<div class="card-body">` trong `<x-admin.card>` | Viết nội dung trực tiếp, card tự bọc |
| HTML thô khi đã có component | Dùng `<x-admin.*>` tương ứng |
| `<i class="fa-*">` FontAwesome | SVG inline Heroicons (outline, stroke-width 2) |
| Hardcode hex color trong class/style | Class Bootstrap (`text-primary`, `bg-body`) hoặc `var(--bs-*)` |
| `<x-admin.scripts.seo-preview>` | **Deprecated**. Dùng `<x-admin.seo-meta>` |
| `<x-admin.language-switcher-widget>` | **Legacy**. Dùng `<x-admin.lang-tabs>` (client-side) |
| Bulk Action nút dùng `variant="secondary"` | Thống nhất `variant="primary"` |

---

## 7. Quy tắc viết Component mới

Khi tạo component mới trong `resources/views/components/admin/`:

### 7.1 Cấu trúc 3 phần bắt buộc

```blade
{{-- 1. PROPS --}}
@props([
    'name'     => null,
    'id'       => null,
    'size'     => 'md',     // 'sm' | 'md' | 'lg'
    'disabled' => false,
])

{{-- 2. LOGIC --}}
@php
    $inputId = $id ?? 'component-' . ($name ?? 'f') . '-' . uniqid();

    $sizes = [
        'sm' => 'form-control-sm',
        'md' => '',
        'lg' => 'form-control-lg',
    ];
    $sizeClass = $sizes[$size] ?? '';
@endphp

{{-- 3. TEMPLATE --}}
<input id="{{ $inputId }}" {{ $attributes->merge(['class' => 'form-control ' . $sizeClass]) }}>
```

### 7.2 Quy tắc

- Mọi prop PHẢI có default value (`=> null`, `=> false`, `=> 'md'`)
- Variant maps dùng **associative array** ánh xạ sang class Bootstrap (không dùng if/elseif)
- Form element PHẢI tự sinh `id` nếu không được truyền
- PHẢI forward `$attributes` vào element gốc (tương thích `wire:model`, `data-*`...)
- `<label for>` phải liên kết đúng với `<input id>`
- Dùng class Bootstrap semantic cho Dark Mode (`bg-body`, `text-body`, `border`)

---

## 8. Checklist trước khi hoàn thành

- [ ] Trang đúng kiểu (Index hoặc Form) và đúng cấu trúc template chuẩn?
- [ ] Dùng đúng `<x-admin.*>` component cho mọi thành phần có sẵn?
- [ ] Không có class Tailwind nào (`space-y-*`, `dark:*`, `grid-cols-*`, `w-*`, `gap-y-*`)?
- [ ] `<x-admin.card>` không bị lồng thêm `.card-body`?
- [ ] Grid form dùng `row g-4` → `col-md-8 col-lg-9` + `col-md-4 col-lg-3`?
- [ ] Đa ngôn ngữ dùng `<x-admin.lang-tabs>` (client-side)?
- [ ] Form có `<x-admin.publish-box>` ở sidebar?
- [ ] SEO dùng `<x-admin.seo-meta>`, KHÔNG dùng `seo-preview`?
- [ ] Bulk Action nút "Áp dụng" dùng `variant="primary"`?
- [ ] Mọi text hiển thị qua `__()` helper?
- [ ] Icon dùng SVG Heroicons inline, không FontAwesome?
- [ ] Component mới có đủ 3 phần (props/logic/template) và forward `$attributes`?
