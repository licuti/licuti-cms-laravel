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