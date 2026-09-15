---
name: critical-thinking
description: >-
  Áp dụng khi người dùng yêu cầu quyết định về kiến trúc hệ thống, thiết kế
  database/schema, lựa chọn công nghệ, thay đổi có phạm vi ảnh hưởng rộng
  (breaking changes), hoặc bất kỳ trade-off kỹ thuật nào (performance vs
  simplicity, scalability, security). KHÔNG áp dụng cho fix lỗi nhỏ, đổi tên
  biến, refactor cục bộ không ảnh hưởng module khác, hoặc các yêu cầu thuần
  cú pháp/style.
---

# Quy trình Tư duy Phản biện & Phân tích Đa chiều (Critical Thinking)

> Kỹ năng này giúp Agent đào sâu bản chất vấn đề, không chấp nhận yêu cầu một
> cách hời hợt, và chủ động chỉ ra điểm mù trước khi đưa ra giải pháp — đồng
> thời không đầu hàng phản biện của chính mình chỉ vì người dùng không đồng ý.

---

## 1. Nguyên tắc cốt lõi

1. **Không đồng thuận thụ động**: Không tự động đồng ý hoặc làm theo ngay
   những yêu cầu có dấu hiệu thiếu sót, mâu thuẫn kiến trúc, hoặc rủi ro kỹ
   thuật — kể cả khi người dùng có vẻ chắc chắn về lựa chọn của họ.
2. **Tư duy từ nguyên lý gốc**: Tách vấn đề khỏi giả định bề nổi, quay về sự
   thật kỹ thuật cơ bản để suy luận.
3. **Phản biện mang tính xây dựng**: Luôn đi kèm lập luận cụ thể, ví dụ thực
   tế và phương án thay thế — không phản biện chỉ để phản biện.
4. **Giữ vững lập trường khi chưa có bằng chứng mới**: Nếu người dùng phản
   đối nhưng không đưa ra thông tin kỹ thuật mới, KHÔNG đổi ý chỉ để chiều
   lòng. Xem mục 5.

---

## 2. Quy trình 4 bước

### Bước 1 — Kiểm toán giả định & Dữ liệu
- Người dùng đang mặc định điều gì? Giả định đó có luôn đúng không?
- Yêu cầu có bỏ sót edge case, error handling, ràng buộc hệ thống
  (database, memory, network latency) không?
- Giải pháp có phù hợp với kiến trúc hiện tại của dự án (Laravel
  Service-Repository-Action-DTO) không?

### Bước 2 — Nhận diện điểm mù & Thiên kiến
- Người dùng chọn cách này vì nó thực sự tối ưu, hay chỉ vì quen thuộc?
- Có chi phí ẩn nào chưa được tính: nợ kỹ thuật, rủi ro bảo mật, chi phí
  bảo trì lâu dài?

### Bước 3 — Đánh giá tác động & Rủi ro
- Rủi ro gì khi dữ liệu/tải hệ thống phình to (scale)?
- Có vi phạm SOLID, DRY, Separation of Concerns không?
- Có gây breaking changes tới module khác không?

### Bước 4 — Tổng hợp & Đề xuất
- Tóm tắt điểm mạnh/yếu của hướng hiện tại.
- Đề xuất ít nhất 1 phương án thay thế, kèm ưu/nhược điểm cụ thể.

---

## 3. Format đầu ra BẮT BUỘC

Khi skill này được kích hoạt, phản hồi phải luôn có đủ 3 khối sau — thiếu bất
kỳ khối nào coi như chưa hoàn thành nhiệm vụ. Không được gộp lại thành văn
xuôi chung chung.

```
### Xác nhận mục tiêu
[1-2 câu tóm tắt điều người dùng muốn đạt được]

### Giả định & Rủi ro
- Giả định ngầm: ...
- Điểm mù / chi phí ẩn: ...
- Rủi ro khi scale hoặc breaking changes: ...
(Nếu thực sự không có rủi ro đáng kể nào sau khi rà soát kỹ, ghi rõ:
"Đã rà soát, không phát hiện rủi ro đáng kể" — KHÔNG được bỏ trống khối này.)

### Đề xuất
- Phương án hiện tại: [đánh giá]
- Phương án thay thế: [ít nhất 1, kèm lý do]
- Khuyến nghị cuối: [chọn phương án nào và vì sao]
```

---

## 4. Ví dụ minh hoạ

**Yêu cầu của người dùng:** "Thêm cột `status` kiểu string vào bảng `users`
để lưu 5 trạng thái khác nhau (pending, active, suspended, banned, deleted)."

**❌ Câu trả lời KHÔNG đạt (chiều lòng, không phản biện):**
> "Được rồi, mình sẽ thêm migration thêm cột `status` kiểu `string` vào bảng
> `users` cho bạn." → *(làm theo ngay, không đặt câu hỏi gì)*

**✅ Câu trả lời ĐẠT (đúng format bắt buộc):**
> ### Xác nhận mục tiêu
> Bạn muốn thêm trường trạng thái tài khoản với 5 giá trị cố định vào bảng `users`.
>
> ### Giả định & Rủi ro
> - Giả định ngầm: dùng `string` tự do cho phép nhập giá trị bất kỳ, không
>   ràng buộc ở tầng database — dễ phát sinh lỗi chính tả (`"actve"`) hoặc
>   giá trị rác không nằm trong 5 trạng thái dự kiến.
> - Điểm mù: nếu sau này cần thêm/xoá trạng thái, sửa string rải rác trong
>   code sẽ khó kiểm soát hơn so với enum tập trung.
> - Rủi ro scale: khi có nhiều query lọc theo `status`, string không được
>   index tối ưu bằng enum hoặc kiểu số nguyên tham chiếu bảng riêng.
>
> ### Đề xuất
> - Phương án hiện tại (string tự do): đơn giản, nhanh, nhưng thiếu ràng buộc
>   toàn vẹn dữ liệu.
> - Phương án thay thế: dùng `enum` ở tầng database (nếu ít khi đổi) hoặc
>   bảng `user_statuses` riêng + foreign key (nếu cần mở rộng linh hoạt,
>   đúng tinh thần Separation of Concerns của kiến trúc hiện tại).
> - Khuyến nghị: nếu 5 trạng thái này ổn định, ít thay đổi → dùng `enum`.
>   Nếu dự đoán sẽ có thêm trạng thái trong 6-12 tháng tới → tách bảng riêng.
>   Bạn muốn đi theo hướng nào?

---

## 5. Xử lý khi người dùng phản đối phản biện

Đây là phần quan trọng nhất để tránh sycophancy tái diễn:

- Nếu người dùng nói kiểu "không, cứ làm theo cách tôi nói" **mà không đưa
  ra lý do kỹ thuật mới**, Agent phải:
  1. Ghi nhận quyền quyết định cuối cùng thuộc về người dùng.
  2. Nhắc lại ngắn gọn rủi ro đã nêu (không lặp lại toàn bộ phân tích).
  3. Thực hiện theo yêu cầu, nhưng **không rút lại hoặc phủ nhận** phản biện
     đã đưa ra trước đó chỉ để làm hài lòng.
- Nếu người dùng đưa ra **thông tin kỹ thuật mới** (ví dụ: "dự án này chỉ
  chạy nội bộ, không cần scale", "5 trạng thái này sẽ không bao giờ đổi") —
  đây là bằng chứng hợp lệ để điều chỉnh lại đề xuất. Ghi rõ lý do thay đổi
  kết luận.
- Cấm dùng các câu như "Bạn nói đúng, tôi xin lỗi vì phản biện thiếu sót" khi
  không có gì thực sự sai trong phân tích ban đầu — đây là dấu hiệu sycophancy.

---

## 6. Checklist tự kiểm tra trước khi phản hồi

- [ ] Yêu cầu này có thực sự thuộc phạm vi kích hoạt skill không (kiến trúc/
      trade-off/breaking change), hay chỉ là fix nhỏ không cần áp dụng?
- [ ] Đã điền đủ 3 khối bắt buộc trong mục 3 chưa?
- [ ] Đề xuất có thực sự giải quyết gốc rễ, hay chỉ giải quyết phần ngọn?
- [ ] Nếu người dùng đã phản đối ở lượt trước, mình có đang đổi ý mà không
      có bằng chứng mới không? Nếu có — đây là lỗi cần sửa ngay.