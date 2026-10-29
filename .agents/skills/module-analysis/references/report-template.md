# Report Template — Mẫu báo cáo phân tích module

Độ dài mục tiêu: **≤ ~60 dòng** (Standard). Người đọc phải nắm được kết luận
trong 2 phút đầu. Chi tiết dài → đưa xuống cuối hoặc file riêng, không chen vào thân.

---

## Khung bắt buộc

```markdown
# Phân tích module: {Tên}

**Phạm vi:** {loại module} · độ sâu {Quick/Standard/Deep} · lens: {L1, L2, ...}
**Giả định:** {nếu có, 1 dòng}

## 1. Kết luận nhanh (TL;DR)
**Đánh giá:** 🟢 Vững / 🟡 Dùng được nhưng có rủi ro / 🔴 Có lỗi nghiêm trọng
- {1 câu: module làm gì & bảo vệ bất biến nào}
- {1 câu: vấn đề lớn nhất & hậu quả kinh doanh}
- {1 câu: việc nên làm đầu tiên}

## 2. Bản đồ module (rất ngắn)
{Sơ đồ luồng chính 3–6 bước, hoặc bảng: Thực thể | Vai trò | Bất biến}
Bất biến chính: I1 …, I2 …  (mỗi cái → "bảo vệ ở: DB / TX+lock / validation / chưa bảo vệ")

## 3. Phát hiện (xếp theo mức độ, tối đa 7)
| # | Mức | Tin cậy | Vấn đề (hậu quả trước) | Bằng chứng | Cách sửa |
|---|---|---|---|---|---|
| 1 | P0 | Cao | … | `file.php:42` | … (1–2 dòng) |

### Chi tiết P0/P1 (mỗi mục ≤ 6 dòng)
**#1 {Tên ngắn}** — Xác minh: {đọc code / test / query}
- **Hiện tượng:** … · **Kịch bản:** A làm …, B làm … → …
- **Nguyên nhân gốc:** …
- **Cách sửa:** … · **Công sức:** S/M/L

## 4. Làm tốt (không cần đụng)
- … (2–4 gạch, kèm bằng chứng ngắn)

## 5. Chưa xác minh được
- … (điều kiện cần để xác minh) — hoặc "Không có"

## 6. Lộ trình đề xuất
1. {P0 …} → 2. {P1 …} → 3. {gom P2 …} · Nhận xét nhỏ: {gộp 1 dòng}

> Bạn muốn xử lý mục nào? (mình chưa sửa gì)
```

---

## Quy tắc điền

- **Hậu quả trước, kỹ thuật sau** trong cột "Vấn đề". ✅ "2 khách cùng mua suất cuối → bán lố" ❌ "Thiếu lockForUpdate".
- **Bằng chứng** luôn là `file:dòng` (link được). Không có bằng chứng → không đưa vào bảng.
- **Cách sửa** nói *hướng* (1–2 dòng), không dán code dài; code chỉ khi người dùng yêu cầu sửa.
- **Công sức**: S (<1h) · M (nửa ngày) · L (>1 ngày/ảnh hưởng nhiều module).
- Không có phát hiện P0/P1 → nói thẳng "Không phát hiện lỗi nghiêm trọng", đừng bịa cho đủ.
- Docs lỗi thời → 1 dòng P3 trong bảng ("Docs {file} lệch code ở …").

---

## Ví dụ minh hoạ (rút gọn) — module giả định "FlashSale"

```markdown
# Phân tích module: FlashSale
**Phạm vi:** Giao dịch · Standard · lens L1, L2, L3, L7
## 1. Kết luận nhanh
**Đánh giá:** 🟡 Dùng được nhưng có rủi ro
- Quản lý chiến dịch giảm giá theo khung giờ, giới hạn số suất bán (quota).
- Rủi ro lớn nhất: quota chưa được trừ nguyên tử → có thể bán vượt suất khi đông khách.
- Làm đầu tiên: trừ quota bằng `UPDATE … WHERE sold + ? <= limit` trong transaction checkout.
## 3. Phát hiện
| # | Mức | Tin cậy | Vấn đề | Bằng chứng | Cách sửa |
|---|---|---|---|---|---|
| 1 | P1 | Vừa | 2 khách cùng mua suất cuối → bán lố quota | `ReserveAction.php:38` (đọc rồi mới cộng) | Cập nhật có điều kiện + kiểm `affected rows` |
| 2 | P1 | Cao | Huỷ đơn có thể hoàn quota 2 lần | `CancelOrder.php:61` (không kiểm trạng thái redemption) | Chuyển trạng thái `reserved→released` có điều kiện |
| 3 | P3 | Cao | Docs `flash-sale.md` ghi "Shell" nhưng đã hoàn thành | `docs/modules/flash-sale.md:3` | Cập nhật docs |
## 4. Làm tốt
- `variant_key` UNIQUE chặn trùng sản phẩm trong cùng chiến dịch (`…000003_*.php:21`).
- Khoá dòng theo thứ tự cố định trước khi trừ kho → tránh deadlock (`PlaceOrderAction.php:72`).
```
