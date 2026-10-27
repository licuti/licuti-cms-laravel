# Module: Cart (Giỏ hàng)

> ✅ **Hoàn thành** (10/2026) | Profile: Full | Tầng 5 — Giao dịch
> Route: `admin.carts.*` (Admin Read-Only) & `api.v1.cart.*` (Storefront/Client API)
> Views: `resources/views/admin/carts/` (Bootstrap 5.3 + SCSS chuẩn Admin UI)
> Test: 100% PASS (`CartApiTest`, `CartMergeTest`, `CartAdminTest`, `CartPricingServiceTest`, `CartCommandsTest`, `CheckoutTest`, `OrderCancelRestoresStockTest`)

---

## 1. Tổng quan & Kiến trúc

Module Cart là trung tâm tính toán và lưu vết trải nghiệm mua sắm của khách hàng (cả khách vãng lai và thành viên đã đăng nhập). Hệ thống tuân thủ nghiêm ngặt mô hình kiến trúc phân lớp:
`Controller → FormRequest / DTO → Service / Action → Repository → Model`

```
Storefront API / Admin Web
         │
         ▼
CartController / CartAdminController
         │
         ▼
CartService / CartAdminService / PlaceOrderAction
         │
         ├─► CartItemGuard (kiểm tra tồn kho & hợp lệ sản phẩm)
         ├─► CartPricingService (tính toán realtime, phát hiện lệch giá)
         │       ├─► PriceResolverInterface (DefaultPriceResolver)
         │       └─► ShippingFeeCalculatorInterface (FlatProductShippingCalculator)
         ├─► CouponValidationService (áp dụng & xác thực voucher)
         │
         ▼
CartRepository / CartItemRepository
         │
         ▼
Database: carts, cart_items
```

---

## 2. Thiết kế Database & Chống Race Condition

### 2.1 Bảng `carts`
- `id` (bigint unsigned, PK)
- `uuid` (uuid, indexed, bảo mật API)
- `user_id` (bigint unsigned, FK nullable, `users.id` cascade on delete)
- `coupon_id` (bigint unsigned, FK nullable, `coupons.id` null on delete)
- `guest_email` (varchar(255), nullable)
- `currency` (char(3), default 'VND')
- `status` (enum: `active`, `abandoned`, `converted`, `merged` qua enum `CartStatus`)
- `converted_order_id` (bigint unsigned, nullable, lưu vết ID đơn hàng đã chốt)
- `last_activity_at` (timestamp, indexed)
- `timestamps`

### 2.2 Bảng `cart_items`
- `id` (bigint unsigned, PK)
- `cart_id` (bigint unsigned, FK `carts.id` cascade on delete)
- `product_id` (bigint unsigned, FK `products.id` cascade on delete)
- `product_variant_id` (bigint unsigned, nullable, FK `product_variants.id` cascade on delete)
- `line_key` (varchar(40), format `p{product_id}-v{variant_id|0}`)
- `quantity` (int unsigned, default 1)
- `unit_price_snapshot` (decimal(15,2), ghi nhận giá lúc thêm vào giỏ)
- `meta` (json, nullable)
- `timestamps`
- **Unique constraint**: `UNIQUE (cart_id, line_key)` — chống race condition gây duplicate dòng hàng khi cùng request gọi đồng thời trong MySQL (kể cả khi `product_variant_id` là null).

### 2.3 Bảng `orders` (Coupon Snapshot)
- `coupon_id` (bigint unsigned, FK nullable)
- `coupon_code` (varchar(50), snapshot mã coupon tại thời điểm đặt hàng)

---

## 3. Quản lý Phiên & Token Khách Hàng

- **Header chuẩn:** `X-Cart-Token: {cart_uuid}`
- **Khách vãng lai (Guest):** Nếu client chưa gửi `X-Cart-Token`, hệ thống tự sinh Cart mới và trả header `X-Cart-Token` trong response.
- **Thành viên đăng nhập (User):** Khi login, client gọi API `POST /api/v1/cart/merge` với `guest_cart_token`. `MergeGuestCartAction` sẽ gộp các line_key tương ứng, cộng dồn số lượng (được kiểm soát bởi tồn kho khả dụng) và đánh dấu guest cart là `merged`.

---

## 4. Động cơ tính giá & Cảnh báo biến động (CartPricingService)

Module cung cấp tầng pricing tách rời (`PriceResolverInterface` & `ShippingFeeCalculatorInterface`):
- `CartPricingService::summarize(Cart $cart, ?CouponSubject $subject = null): CartSummary`
- So sánh giữa `unit_price_snapshot` (giá lúc bỏ vào giỏ) và `priceInfo->unitPrice` (giá hiện hành trong catalog).
- Nếu phát hiện giá thay đổi hoặc số lượng tồn kho giảm dưới số lượng trong giỏ, trả cờ cảnh báo `has_price_change`, `has_stock_issue`, `is_out_of_stock` cùng danh sách thông điệp chi tiết `$summary->issues` để giao diện cảnh báo người dùng trước khi tiến hành thanh toán.
- **Chính sách cập nhật snapshot khi Re-add:** Khi người dùng bấm "Thêm vào giỏ" lần nữa đối với sản phẩm đã có trong giỏ, `unit_price_snapshot` được cập nhật theo giá hiện tại, thể hiện việc khách hàng chủ động xác nhận mức giá mới nhất cho dòng sản phẩm gộp. Khi hàng chỉ nằm thụ động trong giỏ và bị đổi giá trên catalog, cảnh báo `price_changed` sẽ được kích hoạt.

---

## 5. Tích hợp Checkout & Hoàn trả kho / Coupon

1. **Chống gian lận giá Client:** Khi đặt hàng (`PlaceOrderAction`), server tự tính lại toàn bộ giá dựa trên DB catalog, không nhận giá tiền từ client.
2. **Trừ kho Atomic:** Trừ tồn kho qua `StockService::decrementStock` có `lockForUpdate`.
3. **Hoàn kho & Coupon khi hủy đơn:** Khi đơn hàng chuyển trạng thái sang `OrderStatus::CANCELLED`, event `OrderCancelled` được kích hoạt. Listener `ReleaseOrderStockAndCoupon` tự động:
   - Cộng trả lại tồn kho qua `StockService::incrementStock`.
   - Hoàn lại lượt sử dụng mã giảm giá và xóa record trong `coupon_users` qua `CouponValidationService::releaseUsage`.

---

## 6. Danh sách API Endpoints (`/api/v1/cart`)

Tất cả API đều yêu cầu hoặc tự sinh header `X-Cart-Token`:
- `GET /api/v1/cart`: Lấy thông tin giỏ hàng & tính toán tổng tiền, chiết khấu, cảnh báo giá.
- `POST /api/v1/cart/items`: Thêm sản phẩm vào giỏ (`product_uuid`, `variant_uuid`, `quantity`).
- `PATCH /api/v1/cart/items/{item_uuid}`: Cập nhật số lượng item (IDOR protected: chỉ sửa item thuộc giỏ tương ứng).
- `DELETE /api/v1/cart/items/{item_uuid}`: Xóa item khỏi giỏ.
- `DELETE /api/v1/cart`: Xóa toàn bộ sản phẩm trong giỏ.
- `POST /api/v1/cart/coupon`: Áp dụng mã giảm giá (`code`, `guest_email`).
- `DELETE /api/v1/cart/coupon`: Hủy áp dụng mã giảm giá.
- `POST /api/v1/cart/merge`: Gộp giỏ hàng khách vãng lai sau khi đăng nhập (`guest_cart_token`).

---

## 7. Giao diện Quản trị Admin (`/admin/carts`)

Admin panel đóng vai trò theo dõi giỏ hàng & chăm sóc khách hàng (Read-Only kết hợp xóa giỏ rác):
- **Trang danh sách (`admin.carts.index`):**
  - KPI Cards: Tổng giỏ hàng, Giỏ đang hoạt động (Active), Giỏ bị bỏ quên (Abandoned), Giỏ đã chốt đơn (Converted).
  - Bộ lọc: Trạng thái, loại khách (Khách vãng lai / Thành viên), ngày cập nhật, tìm kiếm email/phone/uuid.
  - Phân trang & Bulk Action: Xóa các giỏ hàng đã chọn.
- **Trang chi tiết (`admin.carts.show`):**
  - Xem danh sách sản phẩm, số lượng, giá snapshot vs giá hiện hành.
  - Chi tiết coupon đang áp dụng, tổng tiền tạm tính, phí vận chuyển ước tính, tổng giảm giá.
  - Thông tin khách hàng, thời gian hoạt động cuối cùng.
  - Nút xóa giỏ hàng hoặc quay lại danh sách.
- **Phân quyền:**
  - `carts.view`: Xem danh sách & chi tiết giỏ hàng.
  - `carts.delete`: Xóa giỏ hàng đơn lẻ & xóa hàng loạt.

---

## 8. Tự động hóa & Lịch trình (Console Commands)

- **`cart:mark-abandoned`**: Quét các giỏ hàng `active` có `last_activity_at` quá thời gian quy định (mặc định 24h) và chuyển thành `abandoned`. Chạy định kỳ mỗi giờ (`hourly`).
- **`cart:purge`**: Quét dọn các giỏ hàng cũ không hoạt động (khách vãng lai > 30 ngày, thành viên > 90 ngày) để giải phóng dung lượng DB. Chạy định kỳ hàng ngày (`daily`).
- Cấu hình linh hoạt qua `config/cart.php`.
