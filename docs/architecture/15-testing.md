# 15 — Testing

> Mục tiêu: đảm bảo Service / Repository / Controller hoạt động đúng, đặc biệt là logic transaction, slug unique và bulk actions.

---

## Công cụ

- **PHPUnit** (đã có sẵn trong `composer.json`).
- **Laravel Testing Utilities** (`RefreshDatabase`, `actingAs`, `assertDatabaseHas`...).

## Cấu trúc thư mục

```
tests/
├── Feature/                  ← Test end-to-end qua HTTP
│   ├── Admin/
│   │   └── PostCrudTest.php
│   └── Api/
│       └── V1/
│           └── AuthTest.php
├── Unit/                     ← Test đơn vị (Service, Repository, Model)
│   ├── Services/
│   │   └── PostServiceTest.php
│   └── Repositories/
│       └── PostRepositoryTest.php
└── TestCase.php              ← Base class cho mọi test
```

---

## 1. Test Service (Unit)

Mục tiêu: kiểm tra business logic, transaction, slug unique.

```php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Language;
use App\Services\Admin\Post\PostService;
use App\DTOs\Post\PostDTO;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PostService::class);
        Language::factory()->create(['code' => 'vi', 'is_default' => true]);
    }

    public function test_create_post_generates_unique_slug(): void
    {
        $dto = new PostDTO(
            status: 'published',
            authorId: null,
            image: null,
            publishedAt: now(),
            translations: [
                'vi' => ['title' => 'Bài viết đầu tiên', 'slug' => null],
            ],
        );

        $post = $this->service->create($dto);

        $this->assertNotNull($post->translations->first()->slug);
        $this->assertDatabaseHas('post_translations', ['slug' => 'bai-viet-dau-tien']);
    }

    public function test_create_post_with_duplicate_slug_appends_suffix(): void
    {
        // Tạo bài viết đầu tiên
        $this->service->create(new PostDTO(
            status: 'published', authorId: null, image: null, publishedAt: null,
            translations: ['vi' => ['title' => 'Trùng slug', 'slug' => 'trung-slug']],
        ));

        // Tạo bài viết thứ hai cùng slug
        $post2 = $this->service->create(new PostDTO(
            status: 'published', authorId: null, image: null, publishedAt: null,
            translations: ['vi' => ['title' => 'Trùng slug', 'slug' => 'trung-slug']],
        ));

        $this->assertEquals('trung-slug-1', $post2->translations->first()->slug);
    }

    public function test_update_uses_transaction(): void
    {
        $post = Post::factory()->create();

        $this->service->update($post->uuid, new PostDTO(
            status: 'archived', authorId: null, image: null, publishedAt: null,
            translations: [],
        ));

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'status' => 'archived']);
    }
}
```

---

## 2. Test Repository (Unit)

```php
namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Models\Post;
use App\Repositories\Eloquent\PostRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PostRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(PostRepository::class);
    }

    public function test_getFiltered_filters_by_status(): void
    {
        Post::factory()->count(3)->create(['status' => 'published']);
        Post::factory()->count(2)->create(['status' => 'draft']);

        $result = $this->repo->getFiltered(['status' => 'published']);

        $this->assertEquals(3, $result->total());
    }

    public function test_deleteByIds_returns_count(): void
    {
        $posts = Post::factory()->count(3)->create();

        $count = $this->repo->deleteByIds($posts->pluck('id')->toArray());

        $this->assertEquals(3, $count);
    }
}
```

---

## 3. Feature Test (HTTP)

```php
namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_view_post_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertSee('Danh sách bài viết');
    }

    public function test_admin_can_create_post(): void
    {
        $payload = [
            'status' => 'published',
            'translations' => [
                'vi' => ['title' => 'Test bài viết', 'slug' => 'test-bai-viet'],
            ],
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $payload)
            ->assertRedirect(route('admin.posts.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('post_translations', ['slug' => 'test-bai-viet']);
    }

    public function test_guest_cannot_access_admin(): void
    {
        $this->get(route('admin.posts.index'))->assertRedirect(route('admin.login'));
    }
}
```

---

## 4. Lệnh chạy test

```bash
# Chạy tất cả
php artisan test

# Chạy 1 file
php artisan test --filter=PostServiceTest

# Chạy 1 method
php artisan test --filter=test_create_post_generates_unique_slug

# Chạy với coverage (cần cài extension xdebug)
php artisan test --coverage
```

---

## 5. Quy ước

- **Luôn dùng `RefreshDatabase`** trong mọi test có thao tác DB.
- **Test phải độc lập** — không phụ thuộc thứ tự chạy.
- **Đặt tên method rõ ràng** theo cú pháp `test_{action}_{expected_result}`.
- **Mock Repository khi test Service** nếu Service chỉ chứa logic (không cần test query). Nhưng nếu logic đơn giản thì test end-to-end qua Repository thật cũng được.
- **Coverage mục tiêu:** Service ≥ 80%, Repository ≥ 70%, Controller (Feature test) ≥ 60%.

---

## 6. Test-Case-Spec: Module Page (⏳ viết khi stable — chỉ định nghĩa case)

> **Quyết định 09/2026:** Module Page + refactor đang trong giai đoạn thay đổi nhanh (schema/component
> liên tục). **Hoãn viết file test** — giữ lại **spec** dưới đây. Khi module ổn định (2 release kế tiếp
> không đổi schema), copy format từ `tests/Unit/Services/PostServiceTest.php` + `WithLanguages` trait rồi
> dịch từng case bên dưới thành method test.

### 6.1 Theo dõi tiến độ

| Artifact | Trạng thái |
|---|---|
| `tests/Unit/Services/PageServiceTest.php` | ⬜ Chưa code (spec bên dưới) |
| `tests/Unit/Repositories/PageRepositoryTest.php` | ⬜ Chưa code (spec bên dưới) |
| `tests/Feature/Admin/PageCrudTest.php` | ⬜ Chưa code (spec bên dưới) |
| Template chuẩn có sẵn cho Post/Repository/Feature | ✅ tham chiếu §1-§2-§3 |

### 6.2 `PageServiceTest` — Unit (12 case)

Setup: `RefreshDatabase`, `withLanguages()`, `app(PageService::class)`, `User::factory()->admin()` (nếu check auth).

| # | Method | Scenario | Assertion / Expected |
|---|---|---|---|
| 1 | `test_creates_page_with_translation_and_slug_auto_generated` | create `slug=null`, title "Trang giới thiệu" | `dbHas('page_translations', ['slug' => 'trang-gioi-thieu'])` |
| 2 | `test_creates_page_keeps_user_slug` | create `slug='my-page'` | slug = my-page |
| 3 | `test_slug_duplicate_same_locale_appends_suffix` | tạo 2 page cùng slug gốc | cái 2 slug = `xyz-1` (per-locale) |
| 4 | `test_slug_duplicate_other_locale_ok` | vi và en cùng giá trị slug | cả 2 giữ slug |
| 5 | `test_skips_locale_without_title` | `['en' => ['slug' => 'x'], 'vi' => ['title' => ok]]` | en không được tạo |
| 6 | `test_save_seo_translations_writes_seo_metadata` | translations kèm `meta_title` | `seo_metadata` có row (App\Models\Page, id, locale, meta_title) — **không còn cột nào ở `pages`** |
| 7 | `test_parent_id_null_removes_parent` | update bỏ parent | `parent_id` = null |
| 8 | `test_assert_parent_safe_rejects_self_parent` | update self→self | exception 422 "…chính nó…" |
| 9 | `test_assert_parent_safe_rejects_descendant_parent` | A(root)→B→C, update C→parent=A | 422 "…hệ thống con…" |
| 10 | `test_update_rolls_back_on_failure` | throw giữa transaction | row page + translations + seo không đổi |
| 11 | `test_get_tabs_counts_by_status` | mix 3 published + 2 draft + 1 archived | all.count=6, published.count=3… |
| 12 | `test_get_status_options_and_template_options` | gọi 2 lần | array đúng keys enum |

### 6.3 `PageRepositoryTest` — Unit (8 case)

Setup: `Page::factory()`/`PageTranslation::factory()`, inject `PageRepositoryInterface::class` qua `app()`.

| # | Method | Scenario | Assertion |
|---|---|---|---|
| 1 | `test_get_filtered_filters_by_tab` | tab=published + 3 mixed | `where('status','published')` → total= đúng |
| 2 | `test_get_filtered_status_legacy_param` | `status` (không có `tab`) | hoạt động tương đương |
| 3 | `test_get_filtered_keyword_title` | keyword "giới" | ra trang title chứa; KHÔNG match page khác |
| 4 | `test_get_filtered_per_page_clamp` | per_page=999/1/20/0/null | `paginate(perPage)` = 100/5/20/15/15 |
| 5 | `test_update_status_by_ids_returns_count` | 3 ids → published | return 3; db check status từng row |
| 6 | `test_delete_by_ids_soft_deletes` | 2 ids | deleted_at set, count 0, `trashed` model true |
| 7 | `test_count_by_status` | mix | `[pub=>2,draft=>1,archived=>0 không có mặt hoặc 0]` theo impl |
| 8 | `test_get_tree_excludes_subtree_for_edit` | tree A→B→C(root2 D), exclude B | trả về A? **KHÔNG**: exclude subtree(B)={B,C}; còn A,D; node A._children không chứa B |

### 6.4 `PageCrudTest` — Feature/HTTP (9 case)

Setup: RefreshDatabase, `User::factory()->admin()`, `actingAs`, `WithLanguages` cache flush.

| # | Method | Action | Assertion |
|---|---|---|---|
| 1 | `test_guest_redirected_to_login` | GET `/admin/pages` | 302 → login |
| 2 | `test_index_renders_filter_tabs_and_bulk` | GET `/admin/pages` | 200; thấy `Danh sách`, có filter-tabs count, bulk hidden form `bulk_module=pages` |
| 3 | `test_store_creates_page_softdeletes_ok` | POST `/admin/pages/store` status=draft translations.vi.title | 302 index; `assertSessionHas('success','Thêm trang tĩnh thành công.')`; `page_translations` tồn tại slug auto; `seo_metadata` có row nếu meta_title gửi |
| 4 | `test_store_rejects_invalid_slug` | POST `…slug = "Lỗi dấu!"` | session errors('translations.vi.slug') chứa "chữ thường…gạch ngang" (`regex`) — `prepareForValidation` không biến slug lỗi thành null |
| 5 | `test_store_requires_default_locale_title` | vi.title rỗng | 422 session errors('translations.vi.title') |
| 6 | `test_save_and_edit_returns_to_edit_form` | POST submit `submit_action=save_and_edit` | redirect→edit của entity vừa tạo; form `page->uuid` |
| 7 | `test_update_page_persists` | PUT `/admin/pages/{uuid}` status draft→published; title mới | `assertDatabaseHas('pages', status)`; `assertDatabaseHas('page_translations',['title' => mới])` |
| 8 | `test_destroy_soft_deletes` | DELETE `/admin/pages/{uuid}` | redirect+flash; `assertSoftDeleted('pages')` |
| 9 | `test_bulk_delete_dispatches_registry` | POST `pages.bulk` action=delete ids[] (array string) | 302 back+success flash; all models trashed |
| 10 | `test_bulk_status_change_dispatches_registry` | POST `pages.bulk` action=`status_publish` | `updateStatusByIds` đổi `status` published |

> Ghi chú implement: bulk ids phải **string** theo `BulkActionRequest` (validation `string`);
> `WithLanguages` trait (đã tồn tại ở `tests/Concerns`) dùng được cho Page.