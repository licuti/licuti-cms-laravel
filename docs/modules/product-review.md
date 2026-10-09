# Module: ProductReview

> 🚧 **Hoàn thiện** | Profile: **Production-Ready** | Tầng 4 — Sản phẩm
> Route: `admin.product-reviews.*` | Views: `resources/views/admin/product-reviews/`

## Hiện trạng (Cập nhật 10/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `product_reviews` | ✅ Đã tối ưu schema (is_verified_purchase, helpful_count, softDeletes, composite indexes) |
| Models | ✅ Đầy đủ |
| Repository / Service / Controller / Views | ✅ Đã cấu trúc lại theo chuẩn |
| Events / Enums | ✅ (Sử dụng `ReviewStatus` enum và các Events `ReviewApproved`, `ReviewRejected`) |

## Cấu trúc kỹ thuật

Module đã được nâng cấp lên mức Production-Ready để đảm bảo khả năng scale và toàn vẹn dữ liệu:

### 1. Migration
Các cột chính: `uuid`, `product_id`, `user_id`, `rating`, `content`, `status`, `is_verified_purchase`, `helpful_count`.
- `softDeletes` được tích hợp.
- Tối ưu hóa truy vấn bằng các composite indexes: `['product_id', 'status']` và `['user_id', 'created_at']`.

### 2. Model & Enum
- `ProductReview`: `$fillable`, `HasUuid`, `SoftDeletes`, `casts` (rating integer, is_verified_purchase boolean, helpful_count integer, status -> `App\Core\Enums\ReviewStatus`).
- Sử dụng `ReviewStatus` Enum (pending, approved, rejected, flagged) để ngăn ngừa dữ liệu rác.

### 3. Repository / Service
- Repository: Áp dụng eager loading (`with(['product', 'user'])`) ngay trong query pagination.
- Service: Các hàm `approve`/`reject` kích hoạt các Event `ReviewApproved` và `ReviewRejected` nhằm phục vụ việc mở rộng luồng xử lý không đồng bộ (ví dụ gửi mail, update tổng số sao sản phẩm).

### 4. Giao diện Admin
- Lọc theo status và product.
- Component hiển thị chuẩn Bootstrap 5.3 (`x-admin.*`).
- Tính năng thay đổi trạng thái Duyệt/Từ chối trực tiếp qua row actions.
