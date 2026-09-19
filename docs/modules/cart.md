# Module: Cart

> 🚧 **Shell** | Profile: Full | Tầng 5 — Giao dịch
> Route: `admin.carts.*` | Views: `resources/views/admin/carts/` *(⚠️ theo spec `07` §3.3, Cart KHÔNG có UI Admin — chỉ Service + Repository)*
> Spec: [`07` §3.3`](../07-development-process.md) (MODULE: Cart)

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `carts` | ❌ id+timestamps — thiếu uuid/user_id/session_id |
| Migration **`cart_items`** | ❌ **KHÔNG TỒN TẠI** |
| Models | ❌ |
| FormRequest / DTO / Repository / Service | ✅ (shell) |
| Controller / Views | ✅ (shell) — **không đúng spec** |

## Việc cần làm

### 1. Migration
- `carts`: id, uuid, user_id FK nullable, session_id nullable, timestamps.
- `cart_items`: id, cart_id FK cascade, product_variant_id FK, quantity, unit_price (snapshot), timestamps.

### 2. Models
- `Cart`: `HasUuid`, `items(): HasMany`, `user(): BelongsTo`.
- `CartItem`: `$fillable` (quantity, unit_price), `variant(): BelongsTo`.

### 3. Service (theo spec `07` §3.3)
- `getOrCreateCart(Request $request): Cart` — theo user_id hoặc session_id
- `addItem(Cart $cart, int $variantId, int $qty): CartItem`
- `updateItemQty(int $cartItemId, int $qty): CartItem`
- `removeItem(int $cartItemId): bool`
- `clear(int $cartId): bool`
- `mergeGuestToUser(string $sessionId, int $userId): void` — gọi sau khi user login
- `calculateSummary(Cart $cart): array` — subtotal, item_count

### 4. Cấu trúc lại
- **Xóa `CartController` + `resources/views/admin/carts/`** (theo spec, Cart không có UI Admin) — hoặc giữ nếu có nhu cầu xem giỏ hàng để support khách hàng. Đưa quyết định này vào [`07` §3.3`](../07-development-process.md).
- Đăng ký route API/frontend khi build tầng này.
