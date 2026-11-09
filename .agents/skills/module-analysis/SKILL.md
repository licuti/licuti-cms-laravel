---
name: module-analysis
description: >-
  Áp dụng khi người dùng yêu cầu phân tích, đánh giá, audit, review sâu hoặc
  "soi" MỘT module/tính năng của hệ thống Licuti CMS (ví dụ: "phân tích module
  Cart", "module Order có vấn đề gì không", "đánh giá FlashSale", "audit
  Coupon", "module này thiết kế đúng chưa", "tìm lỗ hổng logic của ..."). Cho ra
  báo cáo kiểu chuyên gia: đúng logic, có bằng chứng file:dòng, xếp hạng rủi ro,
  ngắn gọn dễ hiểu. KHÔNG áp dụng cho fix bug cụ thể, viết code mới, hoặc quyết
  định kiến trúc một chiều (dùng critical-thinking).
---

# Phân tích Module như Chuyên gia (Module Analysis)

> Mục tiêu: người đọc mất **2 phút** để biết module này **đúng/sai ở đâu, nguy
> hiểm cỡ nào, sửa gì trước** — và tin được vì mỗi kết luận có bằng chứng.

Mặc định **chỉ đọc & phân tích, KHÔNG sửa code**. Sau báo cáo, hỏi người dùng muốn xử lý mục nào.

---

## 1. Năm nguyên tắc bất di bất dịch

1. **Code là sự thật, tài liệu chỉ là lời khai.** Mọi nhận định phải đối chiếu
   code/DB thật. Docs lệch code → ghi nhận "docs lỗi thời", KHÔNG kết luận code sai.
2. **Cố bác bỏ trước khi báo cáo.** Mỗi phát hiện phải qua
   [verification-protocol](./references/verification-protocol.md). Chưa chứng
   minh được → hạ xuống "Cần xác minh", không đưa vào danh sách lỗi.
3. **Tín hiệu hơn số lượng.** Tối đa **7 phát hiện** trong báo cáo chính, xếp
   theo mức độ. Vụn vặt/style → gộp 1 dòng "Nhận xét nhỏ". Không liệt kê cho đủ.
4. **Nghiệp vụ trước, kỹ thuật sau.** Bắt đầu từ "module này bảo vệ bất biến
   (invariant) nào của kinh doanh?" (tiền, tồn kho, trạng thái đơn, quota). Lỗi
   làm sai bất biến > lỗi vi phạm convention.
5. **Có hiệu chuẩn.** Mỗi phát hiện ghi rõ **Mức độ** (P0–P3) và **Độ tin cậy**
   (Cao/Vừa/Thấp). Không "dọa" quá mức, không giấu rủi ro thật.

---

## 2. Quy trình 5 pha

### Pha 0 — Xác định phạm vi (≤ 1 câu hỏi)
- Xác định: tên module, **loại** (CRUD-admin / giao dịch / tích hợp ngoài / nội dung), **độ sâu**:
  - `Quick` — 3 lens, chỉ lỗi P0/P1. `Standard` (mặc định) — lens theo loại module. `Deep` — mọi lens + chạy thử.
- Chỉ hỏi lại khi **thực sự mơ hồ** (ví dụ tên module trùng nhiều nơi). Còn lại dùng mặc định và nêu rõ giả định ở đầu báo cáo.

### Pha 1 — Trinh sát (Recon)
1. Kiểm tra KI/tài liệu liên quan: `docs/modules/{module}.md`, `docs/modules/00-module-status.md`.
2. Chạy `powershell -NoProfile -ExecutionPolicy Bypass -File .agents/skills/module-analysis/scripts/map-module.ps1 -Name <Module>` ([script](./scripts/map-module.ps1)) để lập bản đồ file theo tầng, coupling ngoài module và điểm nóng transaction/lock. Tầng nào ghi `(none)` mà lẽ ra phải có (Action, Command dọn dẹp, Exception riêng, tích hợp Checkout…) → ghi vào "không gian âm".
3. Đọc theo thứ tự trong [recon-playbook](./references/recon-playbook.md): **Migration → Model/Enum → Service/Action → Controller/Request → Test**. Đọc **toàn bộ** file lõi, không đoán qua tên hàm.

### Pha 2 — Dựng mô hình tư duy
Viết nháp (không đưa nguyên vào báo cáo) 4 thứ:
- **Thực thể & quan hệ** · **Vòng đời/trạng thái** của tài nguyên chính ·
- **Bất biến** (điều luôn phải đúng) · **Luồng chính + luồng lỗi** (ai gọi ai, transaction bắt đầu/kết thúc ở đâu).

### Pha 3 — Phân tích theo lens
Chọn lens theo loại module (bảng chọn trong [analysis-lenses](./references/analysis-lenses.md)). Với mỗi lens, hỏi 5 câu "chuyên gia":
1. **Đồng thời?** — 2 người/2 request cùng lúc thì sao?
2. **Lặp lại?** — gọi 2 lần (retry, double-click, webhook trùng) có an toàn (idempotent)?
3. **Dở dang?** — chết giữa chừng ở bước N thì dữ liệu còn nhất quán?
4. **Thời gian?** — qua mốc giờ/hết hạn/múi giờ/DST thì hành vi đúng?
5. **Phình to?** — dữ liệu ×100 thì query/lock/cache có chịu nổi?

Và nhìn vào **"không gian âm"**: cái gì *đáng lẽ phải có mà không có* (unique, index, authorize, test cho nhánh lỗi, xử lý huỷ/hoàn).

### Pha 4 — Xác minh
Áp dụng [verification-protocol](./references/verification-protocol.md). Rẻ thì chạy thật: `php artisan test --filter`, query DB bằng tinker, `route:list`. Ghi lại cách đã xác minh.

### Pha 5 — Báo cáo
Dùng đúng [report-template](./references/report-template.md). Ngắn: **≤ ~60 dòng** cho Standard. Lưu thêm file chỉ khi người dùng yêu cầu (đề xuất `docs/analysis/{module}-YYYY-MM.md`).

---

## 3. Quy tắc văn phong (để "ngắn gọn dễ hiểu")

- Câu mở đầu mỗi phát hiện = **hậu quả kinh doanh** ("2 khách cùng mua suất cuối → bán lố"), rồi mới tới nguyên nhân kỹ thuật.
- Một phát hiện = **Hiện tượng → Bằng chứng (`file:dòng`) → Hậu quả → Cách sửa (1-2 dòng)**. Không văn xuôi dài.
- Dùng bảng/gạch đầu dòng; tránh thuật ngữ chưa giải thích; không lặp lại điều đã đúng.
- **Nêu cả điểm tốt** (ngắn) — để người đọc biết cái gì KHÔNG cần đụng.
- Không dùng: "có thể", "có lẽ", "nên xem xét" khi không rõ ràng. Hoặc chắc (kèm bằng chứng), hoặc ghi "Cần xác minh".

---

## 4. Checklist tự kiểm tra trước khi gửi

- [ ] Mọi phát hiện có `file:dòng` thật và đã đọc code đó (không suy từ docs/tên hàm)?
- [ ] Đã cố bác bỏ từng phát hiện (có thể nó đã được xử lý ở tầng khác: DB constraint, middleware, FormRequest)?
- [ ] Phát hiện P0/P1 gắn với một **bất biến nghiệp vụ** bị phá, không chỉ vi phạm style?
- [ ] ≤ 7 phát hiện, xếp theo mức độ; có phần điểm tốt; có thứ tự sửa đề xuất?
- [ ] Phần "Chưa xác minh được" nêu thật thà thay vì đoán?
- [ ] Không sửa code ngoài yêu cầu; đã hỏi người dùng muốn xử lý mục nào?
