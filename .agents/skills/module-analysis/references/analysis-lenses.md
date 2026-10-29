# Analysis Lenses — Các lăng kính phân tích

Không chạy mọi lens như nhau. **Chọn theo loại module**, đào sâu lens quan trọng
nhất, lướt các lens còn lại.

## 0. Bảng chọn lens

| Loại module | Lens bắt buộc (đào sâu) | Lens phụ (lướt) |
|---|---|---|
| **Giao dịch** (Cart, Order, Payment, Coupon, FlashSale, Inventory) | L1 Logic, L2 Dữ liệu, **L3 Đồng thời**, L7 Lỗi | L4, L5, L6, L9, L10 |
| **CRUD Admin** (Brand, Tag, Banner, Menu, Page, Category) | L4 Kiến trúc, L5 Bảo mật, L2 Dữ liệu | L6, L8, L10 |
| **Tích hợp ngoài** (PaymentGateway, Webhook, Shipping) | L5 Bảo mật, **L3 Idempotency**, L7 Lỗi | L1, L9, L10 |
| **Nội dung/đa ngôn ngữ** (Post, Page, Product content) | L2 Dữ liệu, L8 Contract/i18n, L6 Cache | L4, L5 |
| **Nền tảng/RBAC** (User, Role, Setting, Media) | L5 Bảo mật, L4, L2 | L6, L10 |

Ghi `Lens đã dùng: ...` ở đầu báo cáo để người đọc biết phạm vi.

---

## L1 — Logic nghiệp vụ & bất biến
**Hỏi:** Bất biến kinh doanh là gì? Có kẽ hở nào phá được nó?
- Tiền: làm tròn (round half?), `decimal` vs `float`, tính ở server hay tin client, thứ tự áp dụng giảm giá (flash → coupon → ship → thuế), tổng âm?
- Trạng thái: mọi chuyển trạng thái đi qua `canTransitionTo()`? Có đường vòng (update thẳng cột `status`)? Trạng thái cuối có thực sự cuối?
- Biên: `>=` hay `>`, giờ bắt đầu/kết thúc bao gồm hay loại trừ, số lượng = 0, giá = 0, âm.
- Thời gian: múi giờ lưu vs hiển thị (`APP_TIMEZONE`), so sánh `now()` trong/ngoài transaction.
- **Dấu hiệu đỏ:** cùng một quy tắc tính ở 2 nơi (copy-paste) → lệch nhau theo thời gian.

## L2 — Toàn vẹn dữ liệu (Schema)
**Hỏi:** Nếu code sai, DB có chặn được không?
- Bất biến quan trọng có **unique / check / FK** ở DB, hay chỉ ở validation PHP?
- FK `onDelete`: cascade có xoá nhầm lịch sử (đơn hàng, redemption)? `set null` có làm mất truy vết?
- Index khớp truy vấn thực tế (đọc ở Repository)? Composite đúng thứ tự cột?
- Kiểu cột: tiền `decimal(12,2)`; `nullable` đúng ý; `unsigned` cho số lượng; độ dài `varchar` vs dữ liệu thật.
- Soft delete × unique: bản ghi đã xoá mềm có chặn tạo lại không?
- Migration: có thể chạy trên bảng lớn không khoá lâu? Có `down()` đúng?
- **Dấu hiệu đỏ:** "đảm bảo duy nhất" nhưng chỉ bằng `exists()` rồi `create()` (race).

## L3 — Đồng thời & nhất quán (quan trọng nhất cho module giao dịch)
**Hỏi:** 2 request cùng lúc / gọi lặp / chết giữa chừng thì sao?
- **Check-then-act**: `if (stock >= q) { ... decrement }` không có khoá/ điều kiện nguyên tử → bán lố. Đúng: `UPDATE ... WHERE qty >= ?` kiểm `affected rows`, hoặc `lockForUpdate` trong transaction.
- **Thứ tự khoá**: nhiều dòng khoá không theo thứ tự cố định → deadlock. Có retry (`attempts`)?
- **Idempotency**: webhook/checkout/hoàn kho gọi lại có tạo bản ghi thứ 2? Có khoá idempotency (unique key / token)?
- **Ranh giới transaction**: side-effect (gửi mail, bắn event, ghi cache, gọi cổng thanh toán) nằm **trong** TX rồi TX rollback → hiệu ứng không hoàn tác được. Đúng: `afterCommit`, hoặc đưa ra Queue.
- **Hoàn trả**: huỷ/hết hạn có hoàn đúng tài nguyên (kho, coupon, quota)? Có hoàn **2 lần**?
- **Lost update**: đọc-sửa-ghi trên model rồi `save()` thay vì `increment()`/`update` có điều kiện.
- **Dấu hiệu đỏ:** `lockForUpdate()` ngoài transaction (vô tác dụng); `Cache::put` số liệu tồn kho làm nguồn sự thật.

## L4 — Tuân thủ kiến trúc (theo `.agents/AGENTS.md`)
- Controller không chứa business logic / query; Service không query trực tiếp (qua Repository); Action 1 mục tiêu.
- Inject **Interface**, binding trong `RepositoryServiceProvider`; Service/Repository có Interface.
- Hàm ≤ 20–30 dòng, ≤ 3 tham số (DTO), early return; không magic string/number (dùng Enum/Constant).
- Tác vụ nặng → Queue; hành vi phát sinh → Event/Listener; cache dùng `CacheKey` + tag.
- Chỉ ghi vi phạm **có hậu quả** (khó test, khó mở rộng, trùng logic). Vi phạm thẩm mỹ → gộp "Nhận xét nhỏ".

## L5 — Bảo mật & phân quyền
- Mỗi route ghi/đọc nhạy cảm có `authorize()`/permission? Permission đã seed trong `RolePermissionSeeder` và khớp tên?
- **IDOR**: lấy tài nguyên bằng `id`/uuid mà không ràng buộc chủ sở hữu (cart, order, address).
- Không lộ `id` nguyên ra API (dùng `uuid`); `$fillable` (không `$guarded = []`); input qua FormRequest.
- Giá/tổng tiền tính ở server, không tin client; webhook có xác thực chữ ký + chống replay.
- Log không chứa mật khẩu/token/credentials; credentials mã hoá.
- Rate-limit cho endpoint dễ bị lạm dụng (checkout, apply coupon, đăng nhập).

## L6 — Hiệu năng & mở rộng
- N+1 (đọc `->relation` trong vòng lặp/Blade/Resource mà không `with()`).
- Truy vấn lớn không phân trang/`chunk`/`cursor`; `count()` nhiều lần thay vì 1 `GROUP BY`.
- **Hàng nóng (hot row)**: nhiều request cùng cập nhật 1 dòng (quota flash sale) → nghẽn khoá; có cần Redis/atomic counter?
- Cache: key, TTL, **invalidate khi nào**, cache stampede, dữ liệu theo locale đã tách key?
- Việc nặng đồng bộ trong request (PDF, mail, đồng bộ ngoài).
- Ước lượng: dữ liệu ×100, đồng thời ×50 → điểm nào gãy đầu tiên?

## L7 — Chế độ lỗi & vận hành
- Exception nghiệp vụ riêng + `Handler` tập trung? Có chỗ `catch` nuốt lỗi / `return false`?
- Lỗi dở dang để lại **dữ liệu mồ côi** (đơn tạo mà chưa trừ kho; redemption `reserved` mãi)? Có Command/Job dọn dẹp (hết hạn giữ chỗ)?
- Log đủ để điều tra (context: order_id, user, số lượng) mà không lộ bí mật?
- Hành vi khi dịch vụ ngoài chậm/chết (timeout, retry, circuit breaker)?

## L8 — Hợp đồng API/UX & i18n
- Response/lỗi theo chuẩn thật của codebase: `{success, message, data|errors}` (xác minh ở `ApiResponse.php`, **không** tin docs).
- Mã lỗi/HTTP status nhất quán; thông điệp lỗi người dùng hiểu được.
- Text qua `__()`; dữ liệu đa ngôn ngữ ở bảng `_translations`, không hardcode locale.
- Admin UI theo skill `admin-ui` (chỉ khi được yêu cầu soi UI).

## L9 — Coupling & phạm vi ảnh hưởng
- Module bị ai phụ thuộc? Đổi schema/hợp đồng của nó **vỡ** ai (Checkout, Cart, Order, Report)?
- Phụ thuộc vòng (A gọi B, B gọi A)? Module có biết quá nhiều chi tiết nội bộ module khác?
- Thay thế được không (Interface) hay dính chặt vào implementation?

## L10 — Kiểm thử & tài liệu
- Test bảo vệ **bất biến** hay chỉ "trả về 200"? Có test cho: nhánh lỗi, biên, huỷ/hoàn, trùng lặp, phân quyền?
- Có test đồng thời/idempotency cho module giao dịch (hoặc ít nhất test logic điều kiện nguyên tử)?
- Docs (`docs/modules/*.md`, `00-module-status.md`) khớp code? Ghi **docs lỗi thời** như một phát hiện hạng P3 riêng, không trộn với lỗi code.

---

## Mẫu "nhìn vào chỗ không có gì" (negative space)
Tự hỏi từng mục — nếu câu trả lời là *"không có"* thì đáng ghi:
- Unique key ngăn trùng? · Index cho truy vấn nóng? · `authorize()` cho thao tác ghi?
- Test cho nhánh huỷ/hoàn/hết hạn? · Job dọn dữ liệu treo? · Idempotency key?
- Giới hạn trên (max qty, max items, max length)? · Log cho thao tác nhạy cảm?
