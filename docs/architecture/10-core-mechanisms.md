# 10 — Cơ chế hệ thống cốt lõi (Core Mechanisms)

> 4 cơ chế dùng chung toàn hệ thống. Tự viết lại = duplicate code + phá vỡ chuẩn.

---

## 4.1 Hệ thống Đa ngôn ngữ (i18n) & SEO

Toàn bộ các thực thể nội dung (Post, Category, Page, Product...) đều áp dụng cấu trúc đa ngữ:

- **Bảng cha (`posts`, `categories`, `pages`):** Lưu dữ liệu phi ngôn ngữ (`uuid`, `status`, `image`, `author_id`, `published_at`).
- **Bảng dịch (`post_translations`, `category_translations`):** Lưu dữ liệu theo locale (`locale`, `title`, `slug`, `excerpt`, `content`).
  - Đánh chỉ mục: `$table->unique(['post_id', 'locale']);` và `$table->index('slug');`.
- **SEO Metadata (`seo_translations`):**
  - Model sử dụng Trait `App\Traits\HasSeo`.
  - Form Blade nhúng component: `<x-admin.seo-meta :model="$post ?? null" :locales="$activeLanguages" :defaultLocale="$defaultLocale" />`.
  - Service gọi: `$model->saveSeoTranslations($dto->translations);`.

---

## 4.2 Cơ chế Sinh Slug duy nhất (`generateUniqueSlug`)

Khi lưu bản dịch, **luôn gọi** `generateUniqueSlug` từ `BaseService`. Nếu người dùng dán hoặc nhập một chuỗi bất kỳ, hàm sẽ làm sạch bằng `Str::slug()` và kiểm tra xem locale đó đã tồn tại slug chưa; nếu trùng sẽ tự động thêm hậu tố `-1`, `-2`:

```php
$data['slug'] = $this->generateUniqueSlug(
    translationTable: 'post_translations',
    locale: $locale,
    slug: $data['slug'] ?? null,
    title: $data['title'],
    ignoreForeignId: $post->id,
    foreignKey: 'post_id'
);
```

> ⚠️ **KHÔNG tự viết logic sinh slug** trong Service — luôn dùng helper này để đảm bảo nhất quán.

---

## 4.3 Xử lý Thao tác hàng loạt (`BulkActionRegistry`)

Không tự viết các hàm xử lý bulk riêng lẻ trong Controller. Sử dụng kiến trúc Registry tập trung:

**1. Đăng ký Action** trong `app/Providers/BulkActionServiceProvider.php`:

```php
$registry->register('posts', 'delete', 'Xóa đã chọn', function (array $ids) {
    app(PostRepositoryInterface::class)->deleteByIds($ids);
});
```

**2. Controller** sử dụng:

- Trong `index()`: Lấy options qua `'bulkActions' => $bulkRegistry->getActionOptions('posts')`.
- Trong `bulk()`: Dispatch qua `$registry->dispatch('posts', $request->input('action'), $request->input('ids'))`.

> ⚠️ Method chuẩn là `deleteByIds()` (không phải `deleteManyByIds`).

---

## 4.4 Hệ thống Xác nhận Xóa toàn cục (`form-confirm`)

Tuyệt đối **KHÔNG viết `@push('scripts')`** với script SweetAlert2 riêng ở từng trang `index.blade.php`.

Tất cả đã được quản lý toàn cục trong `resources/js/admin/table-utils.js`.

**Cách 1 — Dùng Component `<x-admin.row-actions>`:** Truyền các key confirm vào action array:

```php
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
```

**Cách 2 — Form xóa thủ công:** Thêm class `form-confirm` và các data attribute:

```html
<form action="..." method="POST" class="form-confirm"
      data-confirm-title="Xóa bản ghi?"
      data-confirm-text="Thao tác này không thể hoàn tác."
      data-confirm-btn="Xóa ngay">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
</form>
```