# Module: Order

> 🚧 **Shell** | Profile: Full | Tầng 6 — Đơn hàng & Thanh toán
> Route: `admin.orders.*` | Views: `resources/views/admin/orders/`
> Spec: [`07` §3.3`](../07-development-process.md) (MODULE: Orders)

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `orders` | ❌ id+timestamps |
| Migration `order_items` | ❌ **KHÔNG TỒN TẠI** |
| Migration `order_status_histories` | ❌ **KHÔNG TỒN TẠI** |
| Migration `shipping_addresses` | ❌ **KHÔNG TỒN TẠI** |
| Models | ❌ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ (shell — chỉ `index` đúng spec, `form` **nên bỏ**, đổi sang `show`) |

## Việc cần làm

### 1. Migration
- `orders`: id, uuid, order_number unique, user_id FK, status (OrderStatus enum), subtotal, discount, shipping_fee, total, coupon_id nullable, payment_method_id nullable, notes, timestamps.
- `order_items`: id, order_id FK cascade, product_variant_id, product_name (snapshot), sku (snapshot), quantity, unit_price.
- `order_status_histories`: id, order_id FK cascade, status, note, created_by, timestamps.
- `shipping_addresses`: id, order_id FK cascade, user_id nullable, name, phone, address, city, district, ward, notes.

### 2. Models
- `Order`: `HasUuid`, `casts` (total decimal, status string→enum), `items()`, `statusHistories()`, `shippingAddress()`, `user()`.
- `OrderItem`, `OrderStatusHistory`, `ShippingAddress`: `$fillable` + relations.

### 3. Repository / Service / Action (theo spec `07` §3.3`)
- `OrderRepository`: `getPaginated`, `findByOrderNumber`, `getPendingOrders`, `getRevenueByPeriod`.
- `OrderService`: `updateStatus(string $uuid, string $status, ?string $note)`, `cancelOrder(string $uuid, string $reason)`, `printInvoice(string $uuid)`.
- `PlaceOrderAction::execute(Cart $cart, User|null $user, CheckoutDTO $dto): Order` — validate stock → tính total → DB transaction → create order + items → decrement stock → clear cart → fire `OrderPlaced` event.

### 4. Giao diện Admin (đặc thù)
- `index.blade.php` — filter theo status/date/customer, bảng đơn hàng.
- **`show.blade.php`** (KHÔNG phải `form.blade.php`) — chi tiết đơn: items, tổng, địa chỉ ship, lịch sử trạng thái, chỉ cập nhật status.
- UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md).
