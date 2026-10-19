---
name: backup-and-sync
description: >-
  Áp dụng khi người dùng yêu cầu: backup database, backup DB và đẩy git,
  sao lưu database, đẩy source lên git kèm backup, hoặc thực hiện lại quy trình
  backup DB định kỳ cho Licuti CMS.
---

# Quy trình Backup Database & Đồng bộ Git (Licuti CMS)

Quy trình chuẩn hóa khi người dùng yêu cầu backup CSDL `licuti-cms-laravel` và đẩy source lên Git.

---

## Các bước thực hiện bắt buộc

### Bước 1 — Dump Database
1. Lấy thông tin kết nối từ file `.env` hoặc MySQL Laragon mặc định:
   - Host: `127.0.0.1` (hoặc `localhost`), Port: `3306`
   - User: `root`, Password: `` (rỗng)
   - Database: `licuti-cms-laravel`
2. Xác định đường dẫn `mysqldump` (thường nằm ở `C:\laragon\bin\mysql\mysql-*\bin\mysqldump.exe` hoặc trong `PATH`).
3. Đặt tên file backup theo ngày hiện tại:
   - Định dạng: `database/backup-licuti-cms-laravel-YYYY-MM-DD.sql`
   - Lệnh mẫu:
     ```powershell
     & "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe" -u root -h 127.0.0.1 -P 3306 --default-character-set=utf8mb4 --routines --triggers "licuti-cms-laravel" --result-file="database/backup-licuti-cms-laravel-YYYY-MM-DD.sql"
     ```

### Bước 2 — Dọn dẹp bản backup cũ (Giữ tối đa 3 bản gần nhất)
1. Liệt kê toàn bộ các file `database/backup-licuti-cms-laravel-*.sql`.
2. Sắp xếp theo ngày / thời gian mới nhất đến cũ nhất.
3. Giữ lại **3 file mới nhất**.
4. Xóa các file cũ hơn:
   - Nếu file đã được Git theo dõi: dùng `git rm <file>`
   - Nếu file chưa được Git theo dõi: dùng `Remove-Item <file>`

### Bước 3 — Chạy Test Suite
- Chạy `php artisan test` để đảm bảo source code không bị lỗi trước khi đẩy lên remote.
- Nếu có lỗi test, dừng lại và thông báo cho người dùng hoặc khắc phục trước khi commit.

### Bước 4 — Commit & Push Git
1. Dọn dẹp các file rác phát sinh trong quá trình test (ví dụ: `storage/layout-check-*.html`).
2. Stage toàn bộ thay đổi: `git add -A`.
3. Tạo commit theo chuẩn Conventional Commits (tham khảo `.agents/AGENTS.md`):
   - Mẫu commit: `chore: backup database (YYYY-MM-DD) and sync latest changes` hoặc `feat: ... and backup database (YYYY-MM-DD)`.
4. Push lên GitHub:
   ```powershell
   git push origin main
   ```
5. Kiểm tra lại `git status` đảm bảo `working tree clean`.
