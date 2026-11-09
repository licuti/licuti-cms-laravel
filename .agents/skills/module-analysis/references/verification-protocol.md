# Verification Protocol — Giao thức xác minh & hiệu chuẩn

Mục tiêu: **không báo cáo sai**. Một phát hiện sai làm mất niềm tin vào cả báo
cáo. Quy tắc: *cố bác bỏ phát hiện của chính mình trước khi người khác làm điều đó.*

---

## 1. Cổng xác minh (mọi phát hiện phải qua 5 câu)

| # | Câu hỏi bác bỏ | Cách kiểm |
|---|---|---|
| 1 | **Mình đã đọc đúng code đó chưa?** (không suy từ tên hàm/docs) | Mở file, ghi `file:dòng`. |
| 2 | **Có tầng khác đã chặn chưa?** | Tìm: DB unique/check/FK (migration), FormRequest rules, middleware route, Policy/permission, Observer, Global Scope, transaction ở caller. |
| 3 | **Caller thực tế có đi vào đường lỗi không?** | `rg` tìm nơi gọi; xem input có thể là giá trị gây lỗi? Nếu không thể xảy ra → hạ mức/loại bỏ. |
| 4 | **Chuẩn mình dùng để phán có đúng không?** | Đối chiếu chuẩn **thật** của codebase (xem §3), không chỉ docs. |
| 5 | **Có tái hiện được không?** | Chạy test/tinker/query DB nếu rẻ; nếu không → ghi "chưa tái hiện, suy luận từ code". |

Kết quả: **Xác nhận** → đưa vào danh sách · **Một phần** → hạ mức, nêu điều kiện kích hoạt · **Bị bác** → bỏ (hoặc ghi vào "Đã rà, không phải lỗi" nếu dễ gây hiểu nhầm).

---

## 2. Thang mức độ (Severity) & độ tin cậy (Confidence)

| Mức | Định nghĩa | Ví dụ |
|---|---|---|
| **P0** | Phá bất biến tiền/kho/bảo mật **đang có thể xảy ra** trong luồng bình thường; mất tiền, bán lố, lộ dữ liệu | Race condition trừ kho; IDOR xem đơn người khác |
| **P1** | Phá bất biến trong điều kiện **có thật nhưng hiếm** (tải cao, retry, thao tác bất thường); hoặc lỗ hổng cần điều kiện | Deadlock khi nhiều dòng; webhook không idempotent |
| **P2** | Không sai kết quả nhưng gây **nợ kỹ thuật/hiệu năng** rõ rệt, khó mở rộng | N+1 trên trang danh sách, logic tính giá trùng 2 nơi |
| **P3** | Vi phạm convention, docs lỗi thời, nhận xét nhỏ | Hàm 40 dòng, docs lệch code |

| Độ tin cậy | Khi nào |
|---|---|
| **Cao** | Đã đọc code + đã tái hiện/test, hoặc bằng chứng không thể tranh cãi |
| **Vừa** | Đã đọc code, logic rõ nhưng chưa chạy thử |
| **Thấp** | Suy luận, phụ thuộc điều kiện chưa kiểm được → đưa vào "Cần xác minh", không xếp P0/P1 |

**Quy tắc cứng:** P0/P1 mà Confidence = Thấp → **không được** đứng trong danh sách chính.
**Cân nhắc khả năng xảy ra × hậu quả**, không chỉ hậu quả: lỗi nghiêm trọng nhưng
không thể kích hoạt = P2/P3.

---

## 3. Chuẩn "sự thật" của dự án Licuti (kiểm ở code, không ở docs)

| Chủ đề | Nguồn sự thật | Ghi chú |
|---|---|---|
| **Response/lỗi API** | `app/Core/Traits/ApiResponse.php`, `.agents/AGENTS.md` §4.2 | Dạng `{success, message, data\|errors}`. `docs/architecture/16-api-conventions.md` từng lệch (`error:{code,...}`) → docs sai, **không** phải code sai. |
| **Kiến trúc phân lớp** | `.agents/AGENTS.md` §1–3 | Controller→Service→Repository→Model; Action 1 mục tiêu. |
| **Permission** | `database/seeders/RolePermissionSeeder.php` | Tên permission thật; `docs/04-permissions.md` có thể thiếu. |
| **Trạng thái module** | code + migration thật | `docs/modules/00-module-status.md` có thể lỗi thời. |
| **Schema thật** | Migration mới nhất + `Schema::getColumnListing` | `docs/03-database-details.md` có thể cũ. |
| **Cache key** | `CacheKey` constant class | Không tin tên key trong docs. |

Khi phát hiện **docs ≠ code**: ghi 1 mục P3 "Docs lỗi thời: ..." và **dùng code làm chuẩn** để đánh giá phần còn lại.

---

## 4. Các nhầm lẫn phổ biến (false positive) cần tự kiểm

- Báo "thiếu unique" nhưng unique đã có ở **migration enhance sau** (đọc đủ mọi migration của bảng).
- Báo "thiếu authorize" nhưng đã chặn bằng middleware nhóm route / `AuthorizesWithPermission` trong FormRequest.
- Báo "N+1" nhưng quan hệ đã `with()` ở Repository, hoặc chỉ 1 bản ghi (trang chi tiết).
- Báo "race condition" nhưng đã nằm trong `DB::transaction` + `lockForUpdate` ở caller.
- Báo "thiếu index" trên bảng nhỏ/ít truy vấn, hoặc cột đã nằm trong composite index.
- Báo "nuốt exception" nhưng có `Log::error` + rethrow.
- Báo "magic string" trong test/seed (không phải code sản xuất).
- Áp chuẩn tương lai/đề xuất như thể đã triển khai.

---

## 5. Ghi nhận cách xác minh

Cuối mỗi phát hiện ghi ngắn: `Xác minh: đọc code` / `đọc code + test X pass/fail` /
`query DB` / `chưa tái hiện`. Giúp người đọc biết mức tin cậy mà không cần hỏi.
