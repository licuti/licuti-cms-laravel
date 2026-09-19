# Module: Payment

> 🚧 **Shell** | Profile: Full | Tầng 6 — Đơn hàng & Thanh toán
> Route: `admin.payments.*` | Views: `resources/views/admin/payments/`
> Spec: [`07` §3.4`](../07-development-process.md) (MODULE: Payments)

## Hiện trạng (audit 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `payments` | ❌ id+timestamps |
| Migration **`transactions`** | ❌ **KHÔNG TỒN TẠI** |
| Models | ❌ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ (shell) |

## Việc cần làm

### 1. Migration
- `payments`: id, uuid, order_id FK, payment_method_id FK, amount, currency, status (pending/success/failed/refunded), gateway_ref, paid_at, timestamps.
- `transactions`: id, payment_id FK cascade, type (charge/refund), amount, gateway_response (json), timestamps.

### 2. Models
- `Payment`: `HasUuid`, `casts` (amount decimal, paid_at datetime), `order()`, `method()`, `transactions()`.
- `Transaction`: `$fillable`, `casts` (gateway_response array).

### 3. Contract + implementations (theo spec `07` §3.4)
- `PaymentGatewayInterface`: `charge(Order, array $paymentData): PaymentResult`, `refund(Payment, float $amount): RefundResult`, `handleWebhook(Request): void`.
- `VNPayGateway`, `StripeGateway`, `CODGateway` implement interface.
- `PaymentService`: `processPayment(string $orderUuid, string $gateway, array $data): PaymentResult`, `handleGatewayWebhook(string $gateway, Request $request): void`.
- Binding gateway theo `PaymentMethod::code` (mở rộng bằng Registry sau này — xem [`18-hook-registry-system`](../architecture/18-hook-registry-system.md)).

### 4. Giao diện Admin
- `index.blade.php` — danh sách payments + filter theo status/gateway, badge trạng thái.
- Chi tiết (modal hoặc show): order ref, gateway, transactions log.
- Webhook routes: `routes/api.php` (không qua admin UI).
- UI: [`13-ui-conventions`](../architecture/13-ui-conventions.md), API: [`16-api-conventions`](../architecture/16-api-conventions.md).
