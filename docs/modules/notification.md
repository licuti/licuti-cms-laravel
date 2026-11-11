# Module: Notification

> **✅ Bản đầu hoàn tất (2026-11-10)** — nền tảng thông báo dùng chung, ecommerce triển khai trước.
> Phạm vi bản đầu: **hộp thông báo cá nhân trong admin + email giao dịch cho khách** bằng mẫu tiếng Việt cố định trong source.
> Chưa làm: SMS, WebSocket/push, gửi marketing, hộp thông báo storefront, CRUD mẫu email, thông báo CMS/tài khoản.

## Hiện trạng đã kiểm chứng (2026-11-10)

| Hạng mục | Trạng thái |
|---|---|
| Migration `notifications` + `notification_deliveries` | Đã tạo và áp dụng local; `NotificationMigrationTest` 13 test MySQL (up/down/up cô lập) |
| Model / DTO / Enum / Repository / Service / Job | Đã triển khai, binding trong `RepositoryServiceProvider` |
| Listener cho 5 sự kiện ecommerce | Đã nối; `NotificationBusinessTest` 17 test |
| Hàng đợi / retry / lease / recovery / purge | Đã triển khai; `NotificationDeliveryTest` 23 + `NotificationQueueTest` 7 + `NotificationConcurrencyTest` 1 |
| Hộp admin + chuông + email | Đã triển khai; `NotificationInboxTest` 18 test; kiểm thử trình duyệt thật (admin + nhân viên, light/dark/mobile) |
| Lịch vận hành | `notifications:recover` mỗi phút, `notifications:purge` hằng ngày 03:30 |
| `email_templates`, `sms_templates` | Không thuộc bản đầu (mẫu cố định, chưa SMS) |

Toàn suite sau khi hoàn tất: **406 test / 1755 assertion PASS** trên MySQL `licuti_cms_test` (mail array, queue sync).
Các con số trên đếm được từ `tests/`; không dùng số của lần backup trước.

## 1. Schema

- `database/migrations/2026_11_10_000001_create_notifications_table.php`:
  UUID `id` primary (chính là định danh công khai, không thêm cột `uuid`), `type`, `notifiable_type`/`notifiable_id` (numericMorphs theo User bigint), `data` text theo Laravel, `read_at`, timestamps precision 6. Index owner + thời gian và owner + chưa đọc + thời gian. Dùng `DatabaseNotification` + quan hệ `Notifiable`.
- `database/migrations/2026_11_10_000002_create_notification_deliveries_table.php`:
  UUID `id`, `event_key`, `recipient_key`, `channel`, `event_type`, `recipient_user_id` nullable (FK `nullOnDelete`), `payload` JSON nullable, `status`, `attempts`, `available_at`, `lease_token`, `lease_expires_at`, `last_enqueued_at`, `sent_at`, `finished_at`, `last_error_code`, timestamps precision 6.
- Unique `(event_key, recipient_key, channel)` ở tầng DB chống tạo trùng ý định gửi (không dùng "exists rồi insert"). `finished_at` chung cho sent/failed/skipped để retention không phụ thuộc `updated_at`. Payload nullable để xóa dữ liệu cá nhân khi hết hạn mà vẫn giữ khóa chống trùng.
- Đã có migration `jobs`, `job_batches`, `failed_jobs`; không tạo lại. Hai `down()` chỉ drop bảng của chính migration.

## 2. Kiến trúc gửi

Luồng: `Sự kiện nghiệp vụ → listener ghi ý định gửi trong DB transaction → commit → job gửi từng người/từng kênh → ghi kết quả → admin xem hộp riêng`.

- Listener chỉ ghi DB (không gọi SMTP). `NotificationService::record()` chạy trong transaction nghiệp vụ; enqueue đặt qua `DB::afterCommit` nên rollback ở checkout/giữ suất flash/coupon xóa luôn ý định gửi. Nếu enqueue lỗi sau commit, delivery còn lại để lệnh recovery phục hồi.
- `DeliverNotificationJob` nhận delivery UUID, claim bằng cập nhật nguyên tử có lease (không giữ row lock khi gọi SMTP). Chỉ chủ lease được hoàn tất. Database notification ghi với UUID cố định theo delivery + đánh dấu sent trong cùng transaction nên retry không sinh bản sao.
- Mail dùng `Notification::sendNow` cho đúng một kênh (không lồng queued notification trong job). `NotificationMailManager` áp timeout SMTP riêng, không đổi config mail toàn cục.
- Chống trùng webhook/event và retry thông thường; **không hứa "exactly-once" tới hộp thư** (transport đã nhận nhưng worker chết trước khi ghi kết quả vẫn có thể gửi lặp khi thử lại).
- Cấu hình `config/notification.php`: queue `default`, max 3 lần, backoff [60, 300]; chuỗi timeout an toàn SMTP 15 < job 30 < lease 60 < retry_after 90. Connection queue phải bền vững (sync/deferred bị từ chối hoặc fallback database).

## 3. Ma trận sự kiện bản đầu

| Sự kiện | Người nhận admin | Email khách | Quy tắc |
|---|---|---|---|
| `OrderPlaced` (mới) | `orders.view` | Xác nhận đã tạo đơn | Phát trong transaction sau khi có order/items/shipping/history; gồm COD, không nói đã thanh toán |
| `PaymentSucceeded` | `payments.view` | Xác nhận thanh toán | Phát cuối transaction ghi thanh toán; một xác nhận mỗi đơn; không suy từ khởi tạo COD/chờ |
| `OrderCancelled` | `orders.view` | Thông báo hủy | Listener thông báo riêng, không đụng listener hoàn kho/coupon/flash (vẫn đồng bộ); email dùng lý do công khai an toàn |
| `ReviewApproved` | Không gửi lại người duyệt | Tác giả đánh giá | Transaction + chuyển trạng thái nguyên tử; duyệt lại không tạo thông báo mới; thiếu user/email thì bỏ qua |
| `LowStockThresholdReached` | `products.view` | Không gửi | Chỉ khi giảm từ trên xuống bằng/dưới ngưỡng dưới lock hiện có; mỗi lần vượt có occurrence UUID ổn định; hồi tồn rồi giảm lại tạo cảnh báo mới |

Người nhận nội bộ phải **active + `admin.access` + quyền module**; đọc quyền tươi từ DB (không dựa cache Spatie hay Gate). Hộp cá nhân luôn giới hạn theo user đăng nhập, kể cả super-admin (owner scope bắt buộc, không chỉ dựa Policy vì có Gate super-admin bypass). Worker kiểm tra lại active/quyền trước khi phát; list/count/read/open lọc quyền hiện tại trước phân trang/cập nhật.

Email khách: ưu tiên email hợp lệ trong địa chỉ giao hàng của đơn, thiếu thì email tài khoản gắn đơn; không tìm account khác bằng email, không tạo user cho guest. Nội dung chốt lúc ghi ý định (snapshot), worker không render lại từ model đã đổi. Email không chứa link admin, ghi chú nội bộ hay raw gateway payload.

**Hoãn:** `PaymentFailed` (webhook chưa phân biệt thất bại đã xác thực với webhook giả/sai chữ ký và chưa xác định người nhận), `OrderShipped`, đánh giá chờ duyệt, CMS, tài khoản.

## 4. Giao diện Admin

- Chuông (`layouts/admin.blade.php` + `resources/js/admin/notifications.js`): số chưa đọc, 5 thông báo gần nhất, thời gian/trạng thái, "Xem tất cả" + "Đánh dấu tất cả đã đọc". Lấy dữ liệu khi tải trang/mở chuông, **không polling/WebSocket**.
- Trang "Thông báo của tôi" (`admin/notifications/index.blade.php`): page-header → tabs Tất cả/Chưa đọc/Đã đọc → lọc nhóm nghiệp vụ (chỉ hiện nhóm người dùng có quyền) → bảng + phân trang 20. "Đánh dấu tất cả" chỉ áp dụng thông báo đang tồn tại và có quyền tại thời điểm xử lý.
- Endpoint trong nhóm auth/admin (`routes/web.php`): GET index, GET feed, POST read-all, POST `{uuid}/read`, POST `{uuid}/open`. FormRequest nhận owner từ session, cấm `user_id` tùy ý; thao tác ghi là POST + CSRF. Open xác minh owner/quyền, đánh dấu đọc rồi chuyển route nội bộ; đối tượng đã xóa → về danh sách kèm cảnh báo.
- Bootstrap 5.3 + `x-admin.*`, light/dark/mobile; render JS bằng `textContent`, URL điều hướng do server tạo từ route cho phép; trạng thái loading/rỗng/lỗi mạng/phiên hết hạn rõ ràng, không tự lặp thao tác ghi.

## 5. Vận hành

- `php artisan notifications:recover` (mỗi phút, `withoutOverlapping`): phục hồi delivery pending hoặc lease hết hạn theo lô hữu hạn, có cooldown chặn enqueue lại; failed hết lần thử không tự hồi sinh.
- `php artisan notifications:purge` (hằng ngày 03:30): inbox đã đọc quá 90 ngày (giữ inbox chưa đọc); payload delivery kết thúc xóa sau 90 ngày; khóa chống trùng giữ 365 ngày. **Bảo đảm chống phát lại có cửa sổ 365 ngày.**
- Queue worker phải chạy với connection bền vững và SMTP cấu hình thật khi nghiệm thu vận hành. Mặc định source mail là `log`/array — **không phải bằng chứng thư tới hộp thư**.

## 6. Giới hạn đã biết

- Chưa xác minh SMTP tới hộp thư thật (chỉ array/log trong test và nghiệm thu local). Cần hộp thư thử được cho phép + worker thật để nghiệm thu phần gửi thư.
- Cảnh báo tồn kho bản đầu chỉ theo đường trừ tồn qua `StockService`; chưa theo dõi mọi chỉnh sửa tồn thủ công, thay ngưỡng, hoặc xây lại Inventory.
- Chưa có job auto-hủy đơn pending quá hạn (liên quan FlashSale), nên thông báo hủy chỉ phát khi đơn thực sự bị hủy.

## 7. File chính

- Nền tảng: `app/Models/NotificationDelivery.php`, `app/Enums/NotificationType.php`, `app/Enums/NotificationDeliveryStatus.php`, `app/DTOs/Notification/NotificationEventData.php`, `app/Repositories/{Eloquent,Interfaces}/Notification*`, `app/Services/Shared/Notification/*`, `app/Jobs/DeliverNotificationJob.php`, `app/Notifications/SnapshotNotification.php`, `config/notification.php`.
- Nghiệp vụ: `app/Events/Order/OrderPlaced.php`, `app/Events/Product/LowStockThresholdReached.php`, `app/Listeners/Notification/*`, `app/Services/Admin/Order/OrderService.php`, `app/Services/Admin/ProductReview/ProductReviewService.php`, `app/Services/Shared/Product/StockService.php`, `app/Http/Controllers/Api/Payment/PaymentWebhookController.php`.
- UI/API: `app/Http/Controllers/Admin/NotificationController.php`, `app/Http/Requests/Admin/Notification/*`, `resources/views/admin/notifications/index.blade.php`, `resources/views/emails/notification.blade.php`, `resources/js/admin/notifications.js`.
- Vận hành: `app/Console/Commands/{Recover,Purge}NotificationDeliveries.php`, `routes/console.php`.
- Test: `tests/Feature/Notification{Migration,Business,Delivery,Queue,Concurrency}Test.php`, `tests/Feature/Admin/NotificationInboxTest.php`.