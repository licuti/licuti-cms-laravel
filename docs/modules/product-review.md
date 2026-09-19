# Module: ProductReview

> 🚧 **Shell** | Profile: **Simple** | Tầng 4 — Sản phẩm
> Route: `admin.product-reviews.*` | Views: `resources/views/admin/product-reviews/`

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `product_reviews` | ❌ id+timestamps — thiếu product_id/user_id/rating/content/status |
| Models | ❌ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ (shell) |

## Việc cần làm

Ứng viên **Simple Profile** (xem [`07` §1.5](../07-development-process.md)) — chủ yếu duyệt/ẩn review, không cần DTO.

### 1. Migration
```php
Schema::create('product_reviews', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->unsignedTinyInteger('rating');      // 1-5
    $table->text('content')->nullable();
    $table->string('status', 20)->default('pending')->index(); // pending/approved/rejected
    $table->timestamps();
});
```

### 2. Model
- `ProductReview`: `$fillable`, `HasUuid`, `casts` (rating integer), `product()`, `user()`.

### 3. Repository / Service
- Repository: chỉ `BaseRepository` + `getFiltered` (lọc theo product/status).
- Service (nếu giữ): `approve/reject` thay vì create thủ công — review do user tạo.

### 4. Giao diện Admin
| Field | Component | Ghi chú |
|---|---|---|
| Bảng: sản phẩm / người đánh giá | text | hiển thị |
| Rating | sao (bootstrap) | hiển thị |
| Nội dung | text | hiển thị |
| Trạng thái | `x-admin.select` / `x-admin.badge` | pending/approved/rejected |
| Actions | `x-admin.row-actions` | duyệt / ẩn / xóa (có confirm) |

UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
