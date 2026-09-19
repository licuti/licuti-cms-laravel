# Module: Coupon

> 🚧 **Shell** | Profile: Full | Tầng 5 — Giao dịch
> Route: `admin.coupons.*` | Views: `resources/views/admin/coupons/`
> Spec: [`07` §3.3`](../07-development-process.md) (MODULE: Coupons & Flash Sales)

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `coupons` | ❌ id+timestamps |
| Migration **`coupon_users`** | ❌ **KHÔNG TỒN TẠI** |
| Models | ❌ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ (shell) |

## Việc cần làm

### 1. Migration
- `coupons`: id, uuid, code unique, type (percent/fixed), value, min_order_value, max_discount_amount, usage_limit, usage_per_user, start_date, end_date, is_active, timestamps.
- `coupon_users`: id, coupon_id FK cascade, user_id FK, order_id FK nullable, timestamps — tracking lần dùng.

### 2. Models
- `Coupon`: `HasUuid`, `casts` (value decimal, dates), `users(): BelongsToMany` (pivot usage).
- `CouponUser`: `$fillable`.

### 3. Repository / Service (theo spec `07` §3.3`)
- `CouponService`:
  - `validate(string $code, float $cartTotal, int $userId): CouponValidationResult` — kiểm tra hiệu lực, giá trị đơn tối thiểu, giới hạn số lần dùng
  - `apply(string $code, Order $order): float` — trả discount_amount
  - `deductUsage(int $couponId, int $userId, int $orderId): void`

### 4. Giao diện Admin
Theo spec [`07` §3.3`](../07-development-process.md):
| Field | Component | name |
|---|---|---|
| Mã coupon | `x-admin.input` | `code` |
| Loại giảm | `x-admin.select` | `type` (percent/fixed) |
| Giá trị | `x-admin.input type=number` | `value` |
| Đơn tối thiểu | `x-admin.input type=number` | `min_order_value` |
| Giảm tối đa | `x-admin.input type=number` | `max_discount_amount` |
| Tổng lần dùng | `x-admin.input type=number` | `usage_limit` |
| Lần/user | `x-admin.input type=number` | `usage_per_user` |
| Ngày bắt đầu/kết thúc | `x-admin.datetime-field` | `start_date`, `end_date` |
| Trạng thái | `x-admin.toggle` | `is_active` |

UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
