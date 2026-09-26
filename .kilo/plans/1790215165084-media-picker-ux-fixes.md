# Kế hoạch sửa chữa & nâng cấp Media Picker

## Bối cảnh & ràng buộc

- Component: `resources/views/components/admin/media-picker.blade.php` (Bootstrap 5.3, modal `modal-xl`, JS inline trong `@pushOnce('scripts')`).
- **Dự án KHÔNG dùng Tailwind** (đã verify: `package.json` không có tailwindcss, `vite.config.js` không có plugin, `layouts/admin.blade.php` chỉ `@vite([...])`). CSS build `public/build/assets/app-*.css` là Bootstrap thuần → mọi class kiểu Tailwind đều chết.
- Backend: `MediaController::index/store/destroy` → `MediaRepository::getPaginatedMedia()` → `Media` model (chỉ có `getUrl()`, `getThumbUrl()`).
- Phía tiêu thụ: `image-upload.blade.php` và `seo-meta.blade.php` đều dùng `e.detail.url` (và `e.detail.uuid`); `image-upload` set `modal.dataset.currentId` nhưng picker **chưa bao giờ đọc**.
- Phạm vi (đã chốt với user): **chỉ media-picker + backend tối thiểu**. Không động vào trang `admin/media/index.blade.php`, không thêm utility chung vào `app.scss`.

---

## Danh sách vấn đề (đã verify)

### P0 — Làm hỏng giao diện / dữ liệu

| # | Vấn đề | Bằng chứng |
|---|---|---|
| 1 | Panel browse không có gap dọc: `gap-y-3` (dòng 8) và `gap-y-2` (dòng 25) là class Tailwind chết → toolbar, lưới, panel chi tiết, footer áp sát nhau | CSS build: `gap-y-3`/`gap-y-2` ABSENT, `row-gap-3` CÓ |
| 2 | Truncate tên file lỗi: `min-w-0` (dòng 52) chết → tên file dài tràn khung kết quả upload | CSS build: `min-w-0` ABSENT |
| 3 | Sidebar "Kích thước" + size ở list-view **luôn trống**: JS đọc `item.human_readable_size` nhưng backend không trả (DB chỉ có cột `size` dạng bytes) | `Media` model không có accessor; controller chỉ thêm `url`/`conversions` |
| 4 | Badge MIME **luôn trống**: JS đọc `item.extension` không tồn tại | model/controller không cung cấp |
| 5 | Filter **Video hỏng**: controller chỉ map `type=image` và `type=document` (`MediaController:26-28`) → chọn Video không lọc gì, trả tất cả | repository đã hỗ trợ `mime_type LIKE 'video/%'` |
| 6 | Lưới tải **ảnh gốc full-size** thay vì thumbnail: controller đã trả `conversions.thumb` (webp 150px) nhưng JS dùng `item.url` ở mọi nơi (dòng 79, 80, 91) | media gốc nặng tới 1.3MB vs thumb 150px |

### P1 — Cải thiện UX

| # | Vấn đề |
|---|---|
| 7  | Folder chỉ mở bằng **double-click** (`media-picker.blade.php:82`); single-click vô tác dụng dù con trỏ kiểu clickable |
| 8  | Xóa file dùng `confirm()` / `alert()` native (`:96`) — dự án có sẵn `AdminUI.confirm()` / `AdminUI.alert()` (SweetAlert2, `resources/js/admin/ui-bridge.js:35,58`) |
| 9  | **Không pre-select** ảnh đã chọn: `image-upload:64` set `modal.dataset.currentId` nhưng picker không bao giờ đọc |
| 10 | Upload `accept` (dòng 40) hẹp hơn nhiều so với validation backend `mimes:jpg,...,svg,pdf,doc,docx,xls,xlsx,csv,zip,rar,txt,mp4` (`MediaController:68`) → không upload được mp4/docx/zip… |
| 11 | `created_at` hiển thị raw `2026-08-09 13:46:15` (sidebar "Ngày tải lên") |
| 12 | `pickerCurrentFolderId` không reset khi mở lại picker (`:100`) → ở lại thư mục cũ giữa các lần mở |

### P2 — a11y / robustness

| # | Vấn đề |
|---|---|
| 13 | 2 tab `role="tab"` thiếu `aria-controls`; 2 panel thiếu `aria-labelledby` (dòng 5-8, 38) |
| 14 | Grid item là `<div>` không `tabindex`/`role`/phím Enter — không chọn được bằng bàn phím |
| 15 | Upload lỗi (vd 422 mime sai) chỉ notify generic "Lỗi khi tải lên" — không parse message server trả |
| 16 | `csrfToken` có thể `null` nếu thiếu meta tag → header gửi chuỗi `"null"` (`:91`) |

---

## Task list (thứ tự thực thi)

### Phase 1 — Backend tối thiểu (API contract)

**T1. `app/Http/Controllers/Admin/MediaController.php` — `index()`**
- Trong closure `transform` (dòng 48-52), ngoài `url`/`conversions`, thêm:
  - `human_readable_size` = format bytes → KB/MB (chia 1024, tối đa 1-2 chữ số thập phân)
  - `extension` = phần mở rộng từ `original_name` (fallback `file_name`), viết hoa
  - `formatted_date` = `$item->created_at?->format('d/m/Y H:i')`
- Lý do làm trong controller thay vì accessor + `$appends` trên model: tránh thay đổi JSON serialization của `Media` ở mọi nơi khác (media index page, API…), giữ blast radius nhỏ.

**T2. `MediaController.php` — `store()`**
- Thêm cùng 3 field đó vào đối tượng `$media` trước `response()->json($media)` (dòng 84-87) để upload-result dùng được.

**T3. `MediaController.php` — `index()`**
- Thêm `if ($type === 'video') $filters['mime_type'] = 'video';` (sau dòng 28) → repository `LIKE 'video/%'` hoạt động.

**Rủi ro T1-T3:** phản hồi JSON thêm field → JS cũ/frontend khác không break (chỉ thêm field, không đổi tên field hiện có). Kiểm tra `admin/media/index.blade.php` không trùng tên field mới (nó tự format size client-side) — cần confirm nhanh khi implement.

### Phase 2 — CSS class chết

**T4. `media-picker.blade.php`**
- Dòng 8: `gap-y-3` → `row-gap-3`.
- Dòng 25: `gap-y-2` → `row-gap-2`.
- Dòng 52: `min-w-0` → thay bằng inline `style="min-width:0"` (không thêm utility vào `app.scss` theo phạm vi đã chốt).

### Phase 3 — Dùng thumbnail, đổ dữ liệu mới

**T5. `media-picker.blade.php` JS**
- Định nghĩa helper: `function itemThumb(item){ return (item.conversions && item.conversions.thumb) ? item.conversions.thumb : (item.url||item.original_url||''); }`
- Dùng `itemThumb(item)` cho: ảnh grid view (`createGridItemEl`, dòng 79), ảnh list view, ảnh sidebar (`updateSidebarInfo`, dòng 80), upload thumb (dòng 91).
- Sidebar "Ngày tải lên" (`:80`) đọc `item.formatted_date || item.created_at`.
- Badge MIME (`:80`) đọc `item.extension` (giữ fallback `''`).
- `human_readable_size` giữ nguyên (field đã có sau T1/T2).
- **Giữ `item.url`** cho input URL (`sidebar-info-url`) và cho `dispatchSelectedEvent` (consumers cần original URL).

### Phase 4 — Tương tác folder, xóa, pre-select, upload

**T6. Folder single-click** — thêm listener click trên `#picker-folder-grid` delegate `.folder-item`: đặt `pickerCurrentFolderId = folder.uuid; loadPickerData(1);` (giữ dblclick hiện tại). Nên thêm class `.folder-item` vào lookup; folder items đã có class `folder-item` (dòng 82).

**T7. Xóa qua AdminUI** — dòng 96: thay `confirm(...)` bằng `AdminUI.confirm({title:'Xóa file?', text:'Bạn có chắc chắn muốn xóa file này vĩnh viễn?', confirmText:'Xóa', cancelText:'Hủy'}, callback)` và `alert(...)` trong catch bằng `AdminUI.alert('Lỗi','Không thể xóa file.','error')`. Giữ guard `typeof AdminUI!=='undefined'`.

**T8. Pre-select ảnh hiện tại** — trong `loadPickerData` sau khi render xong page đầu (không phải append): đọc `modal.dataset.currentId`; nếu có item nào `uuid === currentId` thì chạy cùng logic select (đổi border, hiện check, `updateSidebarInfo`, enable confirm). Tránh duplicate code: tách hàm `selectItem(item, el)` và dùng lại cho cả click handler dòng 89.

**T9. Upload accept** — dòng 40: mở rộng `accept` cho khớp backend: thêm `image/svg+xml,video/mp4,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,text/csv,application/zip,application/x-rar-compressed,text/plain`. Cập nhật chữ dropzone (dòng 42) nếu cần (giữ "Tối đa 10MB").

**T10. Reset folder khi mở lại** — dòng 100: thêm `pickerCurrentFolderId='';` vào handler `global-media-picker-open`.

### Phase 5 — a11y / robustness

**T11. Tabs a11y** — tab browse thêm `aria-controls="picker-panel-browse"`; tab upload thêm `aria-controls="picker-panel-upload"`; panel browse thêm `aria-labelledby="picker-tab-browse"`; panel upload thêm `aria-labelledby="picker-tab-upload"`. (Cũng cân nhắc `data-bs-toggle="tab"`? KHÔNG — đã có JS `switchTab` tự viết, dùng sẽ xung đột.)

**T12. Grid item keyboard** — trong `createGridItemEl`: thêm `el.tabIndex=0; el.setAttribute('role','button');`; thêm `el.addEventListener('keydown', function(e){ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); el.click(); } });`.

**T13. Upload error parse** — nhánh `else` của `xhr.onload` (dòng 91): thử `var res=JSON.parse(xhr.responseText); msg=res.message||(res.errors?Object.values(res.errors)[0]:'Lỗi khi tải lên.');` rồi `AdminUI.notify('error', msg)`. Giữ fallback chuỗi cố định khi parse lỗi.

**T14. csrfToken guard** — dòng 91: `var csrfToken=document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')||'';`.

---

## Validation

1. **Preview tĩnh**: cập nhật `public/_preview-media-picker.html` (đã tạo) phản ánh markup mới để đối mắt kiểm tra cả light/dark, grid/list, panel chi tiết, empty state. Sau khi xong có thể xóa file này.
2. **Flow thủ công** (server đang chạy `127.0.0.1:8000`, DB có 14 media + 1 admin):
   - Mở 1 form có `x-admin.image-upload` → picker mở, grid hiển thị **thumb** (mở DevTools Network xác nhận request là `media/thumb/*.webp`, không phải original).
   - Sidebar: Tên/Kích thước/Ngày tải lên/badge MIME không trống; input URL chứa đường dẫn original.
   - Chọn filter **Video** → chỉ còn video; filter **Tài liệu** → chỉ pdf/docx.
   - Click 1 lần vào folder → vào thư mục; bấm "Tất cả ảnh" quay lại.
   - Mở picker từ 1 trường **đã có ảnh** → ảnh đó được pre-select (viền primary + check).
   - Bấm "Xóa vĩnh viễn" → hiện dialog SweetAlert2 (không phải native confirm).
   - Upload 1 file `.mp4`/`.docx` qua dropzone → thành công; upload file sai mime → toast hiện message cụ thể.
   - Bật dark mode (`data-bs-theme=dark`) → không có vùng sáng/không tương phản.
3. **Regression**: nhanh check `admin/media/index` page vẫn render bình thường (do chỉ thêm field JSON, không đổi field cũ).
4. Nếu có test suite: chạy `php artisan test` (kiểm tra tests/ có test liên quan media trước).
5. Lint: `npm run build` (vite) thành công nếu có thay đổi asset (dự kiến không cần vì chỉ sửa Blade + PHP).

## Lưu ý

- T5 phụ thuộc T1/T2 (field `conversions`/`formatted_date`/`extension`/`human_readable_size` phải có trong response). Nên làm Phase 1 trước.
- T8 cần lưu ý: `mediaItemsMap` key theo `id` (number), nhưng pre-select khớp theo `uuid` → duyệt map hoặc lưu thêm map uuid→item.
- Sau khi sửa, xóa `public/_preview-media-picker.html` và tắt server nền (pid 23956) nếu không cần.
