# Recon Playbook — Trinh sát module

Mục tiêu: hiểu module **nhanh và đúng** mà không đọc lan man. Đọc theo thứ tự
**dữ liệu → quy tắc → luồng → giao diện → test**, vì bất biến nằm ở dữ liệu và quy tắc.

---

## 1. Thứ tự đọc & cần rút ra gì

| # | Tầng | File điển hình (Licuti) | Rút ra |
|---|---|---|---|
| 1 | **Migration** | `database/migrations/*{module}*` | Cột, kiểu, `nullable`, FK + `onDelete`, **unique**, **index**, check constraint. Đây là *hàng rào cuối cùng* của bất biến. |
| 2 | **Enum** | `app/Enums/*` | Tập trạng thái + `canTransitionTo()` → **state machine**. |
| 3 | **Model** | `app/Models/*` | `$fillable`, `casts`, quan hệ, scope, accessor tính toán. Có logic nghiệp vụ lọt vào đây? |
| 4 | **Repository** | `app/Repositories/**` | Query thực tế, `lockForUpdate`, eager load, phân trang. |
| 5 | **Service / Action** | `app/Services/**`, `app/Actions/**` | **Trái tim**: transaction, thứ tự thao tác, khoá, event, cache invalidation. |
| 6 | **Controller + FormRequest** | `app/Http/Controllers/**`, `app/Http/Requests/**` | `authorize()`, rules, cách trả response/lỗi. |
| 7 | **Routes** | `routes/*.php` (+ `php artisan route:list --name=...`) | Middleware (auth, throttle, permission) từng route. |
| 8 | **Tích hợp ngoài module** | grep tên class ở nơi khác | Ai gọi module này? Module này gọi ai? (Checkout, Cart, Order, Observer, Command, Job) |
| 9 | **View / UI** | `resources/views/admin/{module}/**` | Chỉ để kiểm luồng UX và dữ liệu gửi lên; không phân tích CSS trừ khi được hỏi. |
| 10 | **Test** | `tests/Feature/**`, `tests/Unit/**` | Test có bao phủ **nhánh lỗi/đồng thời** hay chỉ happy path? |
| 11 | **Docs** | `docs/modules/{module}.md` | Chỉ để đối chiếu — **docs ≠ sự thật**. |

---

## 2. Lệnh trinh sát nhanh (PowerShell)

```powershell
# Bản đồ file theo tầng (khuyên dùng đầu tiên)
powershell -NoProfile -ExecutionPolicy Bypass -File .agents/skills/module-analysis/scripts/map-module.ps1 -Name FlashSale

# Ai dùng module này ở NGOÀI module? (tìm coupling)
rg -n "FlashSale" app routes --glob "!*FlashSale*"

# Transaction & khoá: module có an toàn đồng thời không?
rg -n "lockForUpdate|DB::transaction|handleTransaction|sharedLock|increment\(|decrement\(" app

# Ai bắn event / dispatch job liên quan?
rg -n "event\(|::dispatch\(|dispatchSync|afterCommit" app

# Cache key & invalidate
rg -n "Cache::|CacheKey::" app

# Route + middleware của module
php artisan route:list --name=flash-sales

# Kiểm tra schema thật trong DB (đối chiếu migration)
php artisan tinker --execute="dump(Schema::getColumnListing('flash_sales'));"
```

Nếu `rg` không có, dùng `Select-String -Path app -Pattern ... -Recurse` hoặc công cụ grep của agent.

---

## 3. Trích xuất "mô hình tư duy" (ghi nháp, ≤ 15 dòng)

```
THỰC THỂ:      A 1-n B, B n-1 C ...
VÒNG ĐỜI:      draft → active → ended   (ai/điều kiện nào chuyển?)
BẤT BIẾN:      I1: sold_quantity <= quantity_limit
               I2: tổng tiền đơn = Σ(dòng) - giảm giá + ship
               I3: 1 (user, coupon) chỉ dùng ≤ N lần
LUỒNG CHÍNH:   Controller → Service::x → [TX: Repo::a → Action::b → event]
LUỒNG LỖI:     b ném lỗi → TX rollback? cache đã đổi chưa? event đã bắn chưa?
PHỤ THUỘC RA:  gọi Stock, Coupon, Payment ...
PHỤ THUỘC VÀO: Checkout, Cart, Admin UI, Command dọn dẹp ...
```

Mỗi bất biến → tự hỏi: **được bảo vệ ở đâu?** (DB constraint / transaction+lock /
validation / chỉ "hy vọng" caller làm đúng). Càng xuống thấp càng chắc;
chỉ bảo vệ ở tầng UI/validation = **yếu**.

---

## 4. Dừng đọc khi nào?

- Đã trả lời được: *bất biến là gì, nằm ở đâu, luồng lỗi ra sao, ai phụ thuộc.*
- Gặp file >300 dòng: đọc theo hàm công khai (public) + hàm có `DB::`/`lock`/`event`; không cần đọc helper thuần format.
- Đừng đọc lại phần đã hiểu; ghi `file:dòng` ngay khi gặp thứ đáng ngờ để khỏi đọc lần 2.
