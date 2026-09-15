# 17 — Git Workflow

> Quy ước làm việc nhóm: branch naming, commit message, PR template, code review.

---

## 1. Branch Strategy: Git Flow đơn giản

```
main              ← Production (chỉ merge từ release/*)
  │
  ├── develop     ← Branch tích hợp (default)
  │     │
  │     ├── feature/short-desc       ← Tính năng mới
  │     ├── bugfix/short-desc        ← Sửa bug
  │     ├── hotfix/short-desc        ← Sửa gấp trên production
  │     └── refactor/short-desc      ← Tái cấu trúc code
  │
  └── release/v1.2.0   ← Chuẩn bị release (chỉ fix bug + bump version)
```

### Quy tắc

- **`main`:** Cực kỳ bảo vệ. Chỉ merge qua PR từ `release/*` hoặc `hotfix/*`. Tag version sau mỗi lần merge.
- **`develop`:** Branch mặc định. Tất cả feature merge vào đây trước.
- **Branch con:** Tạo từ `develop`, merge lại `develop` qua PR.
- **Xóa branch** sau khi merge (cả remote lẫn local).

### Lệnh khởi tạo

```bash
git checkout -b develop main
git push -u origin develop
```

---

## 2. Branch Naming

| Loại | Cú pháp | Ví dụ |
|---|---|---|
| Feature | `feature/{module}/{short-desc}` | `feature/post/add-export-excel` |
| Bugfix | `bugfix/{module}/{short-desc}` | `bugfix/auth/fix-redirect-loop` |
| Hotfix | `hotfix/{short-desc}` | `hotfix/fix-sql-injection-search` |
| Refactor | `refactor/{module}/{short-desc}` | `refactor/post/extract-translation-service` |
| Docs | `docs/{short-desc}` | `docs/split-developer-guide` |

**Quy tắc:**
- Dùng **kebab-case**, không dấu, không space.
- Phần `{short-desc}` tối đa 4-5 từ, mô tả đúng 1 thay đổi.
- Mỗi branch chỉ giải quyết **1 task** — không nhồi nhiều feature.

---

## 3. Commit Message (Conventional Commits)

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Type

| Type | Ý nghĩa |
|---|---|
| `feat` | Tính năng mới |
| `fix` | Sửa bug |
| `docs` | Chỉ thay đổi tài liệu |
| `style` | Format code (không thay đổi logic) |
| `refactor` | Tái cấu trúc code (không thêm tính năng, không sửa bug) |
| `test` | Thêm/sửa test |
| `chore` | Build, CI, dependencies |

### Scope (tùy chọn)

- Tên module: `post`, `auth`, `order`, `api`, `admin`, `docs`...

### Ví dụ

```bash
git commit -m "feat(post): add Excel export for post list"
git commit -m "fix(auth): prevent redirect loop on admin login"
git commit -m "refactor(post): extract translation service from PostService"
git commit -m "docs(architecture): split DEVELOPMENT_GUIDE into 7 files"
```

### Body (cho commit phức tạp)

```
feat(order): add multi-warehouse inventory support

- Add warehouse_id column to inventories table
- Update OrderService to check stock across warehouses
- Add warehouse selector in admin product form

Closes #142
```

> **Quy tắc vàng:**
> - Subject ≤ 72 ký tự, viết thường, không dấu chấm cuối.
> - Body giải thích **TẠI SAO**, không phải CÁI GÌ (code đã cho thấy cái gì).
> - 1 commit = 1 thay đổi logic. Không commit cả file `composer.lock` + 5 file PHP trong 1 lần.

---

## 4. Pull Request Template

Tạo file `.github/pull_request_template.md`:

```markdown
## Mô tả
<!-- Mô tả ngắn gọn thay đổi -->

## Loại thay đổi
- [ ] Feature mới
- [ ] Bug fix
- [ ] Refactor
- [ ] Docs
- [ ] Hotfix

## Liên kết
- Issue: #xxx
- Design: [link Figma nếu có]

## Checklist (theo docs/architecture/14-checklist.md)
- [ ] Controller không chứa query DB
- [ ] Service kế thừa BaseService, inject RepositoryInterface
- [ ] FormRequest đúng loại (Admin vs API)
- [ ] Sử dụng Blade Component chuẩn (không Tailwind)
- [ ] Có test cho logic mới
- [ ] Cập nhật docs liên quan

## Screenshots (nếu có UI)
<!-- Kéo thả ảnh vào đây -->

## Ghi chú cho Reviewer
<!-- Bất kỳ điều gì reviewer cần biết -->
```

---

## 5. Code Review

### Người review (Reviewer)

- **Ít nhất 1 approval** trước khi merge.
- Reviewer **không phải** tác giả PR.
- Ưu tiên review theo thứ tự: kiến trúc → logic → UI → docs.

### Người tạo PR (Author)

- Tự check checklist trước khi gửi.
- PR < 400 dòng thay đổi (nếu hơn → tách thành nhiều PR).
- Mô tả rõ **vấn đề giải quyết**, không chỉ liệt kê file thay đổi.
- Sẵn sàng phản hồi comment trong 24h.

### Cấu trúc comment review

- 🔴 **Blocking:** Bug, vi phạm chuẩn, security — phải sửa trước khi merge.
- 🟡 **Nitpick:** Góp ý nhỏ — có thể bỏ qua nếu có lý do chính đáng.
- 🟢 **Praise:** Khen cách làm tốt.
- ❓ **Question:** Cần tác giả giải thích.

---

## 6. Lệnh hữu ích

```bash
# Cập nhật feature branch với develop mới nhất
git checkout feature/post/export
git rebase develop
# hoặc
git merge develop

# Squash nhiều commit thành 1 trước khi merge PR
git rebase -i HEAD~5
# Trong editor: pick commit đầu, squash các commit sau

# Xem thay đổi giữa 2 branch
git diff develop..feature/post/export --stat

# Xóa branch đã merge
git branch -d feature/post/export
git push origin --delete feature/post/export

# Tag version khi release
git tag -a v1.2.0 -m "Release v1.2.0"
git push origin v1.2.0
```