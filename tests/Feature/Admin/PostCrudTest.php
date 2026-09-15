<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\Post;
use App\Models\User;
use Database\Factories\LanguageFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLanguages;
use Tests\TestCase;

/**
 * Feature test cho HTTP flow CRUD của module Post.
 *
 * Bao phủ:
 *  - Truy cập index (auth + guest)
 *  - Tạo bài viết qua form (POST /admin/posts)
 *  - Cập nhật bài viết qua form (PUT /admin/posts/{uuid})
 *  - Xóa bài viết (DELETE /admin/posts/{uuid})
 *  - Validation fail (status không hợp lệ)
 *  - Submit action: save vs save_and_edit
 */
class PostCrudTest extends TestCase
{
    use RefreshDatabase, WithLanguages;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpLanguages();
        $this->admin = User::factory()->admin()->create();
    }

    /** @test */
    public function guest_cannot_access_admin_posts_index(): void
    {
        $response = $this->get(route('admin.posts.index'));

        $response->assertRedirect(route('admin.login'));
    }

    /** @test */
    public function admin_can_view_posts_index(): void
    {
        Post::factory()->count(3)->withVietnamese()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.index'));

        $response->assertOk();
        $response->assertSee('Danh sách Bài viết');
    }

    /** @test */
    public function admin_can_create_post(): void
    {
        $payload = [
            'status' => 'published',
            'is_featured' => false,
            'translations' => [
                'vi' => [
                    'title' => 'Bài viết mới',
                    'slug'  => 'bai-viet-moi',
                    'content' => 'Nội dung test',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $payload);

        $response->assertRedirect(route('admin.posts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('post_translations', [
            'slug'  => 'bai-viet-moi',
            'title' => 'Bài viết mới',
        ]);
    }

    /** @test */
    public function save_and_edit_redirects_to_edit_page(): void
    {
        $payload = [
            'status' => 'draft',
            'submit_action' => 'save_and_edit',
            'translations' => [
                'vi' => ['title' => 'Bài viết cần sửa tiếp'],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $payload);

        $post = Post::first();
        $response->assertRedirect(route('admin.posts.edit', $post->uuid));
    }

    /** @test */
    public function admin_can_update_post(): void
    {
        $post = Post::factory()->create();
        $post->translations()->create([
            'locale' => 'vi',
            'title'  => 'Cũ',
            'slug'   => 'cu',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.posts.update', $post->uuid), [
                'status' => 'published',
                'translations' => [
                    'vi' => [
                        'title' => 'Mới',
                        'slug'  => 'moi',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.posts.index'));

        $this->assertDatabaseHas('post_translations', [
            'post_id' => $post->id,
            'title'   => 'Mới',
            'slug'    => 'moi',
        ]);
    }

    /** @test */
    public function admin_can_delete_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.posts.destroy', $post->uuid));

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    /** @test */
    public function invalid_status_is_rejected(): void
    {
        $payload = [
            'status' => 'invalid-status-value',
            'translations' => ['vi' => ['title' => 'Test']],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $payload);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseCount('posts', 0);
    }

    /** @test */
    public function missing_default_locale_title_fails_validation(): void
    {
        $payload = [
            'status' => 'draft',
            'translations' => [
                'en' => ['title' => 'Only English title'],
                // VI (default) missing
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $payload);

        $response->assertSessionHasErrors('translations.vi.title');
    }

    /** @test */
    public function admin_can_view_edit_form_with_existing_post(): void
    {
        $post = Post::factory()->create();
        $post->translations()->create([
            'locale' => 'vi',
            'title'  => 'Bai viet mau',
            'slug'   => 'bai-viet-mau',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.edit', $post->uuid));

        $response->assertOk();
        $response->assertSee('Sửa Bài viết');
        // Title và slug xuất hiện trong value attribute
        $response->assertSee('value="Bai viet mau"', false);
        $response->assertSee('value="bai-viet-mau"', false);
    }

    /** @test */
    public function bulk_delete_works(): void
    {
        $posts = Post::factory()->count(3)->create();
        $ids = $posts->pluck('id')->map(fn($id) => (string) $id)->toArray();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.bulk'), [
                'bulk_module' => 'posts',
                'action'      => 'delete',
                'ids'         => $ids,
            ]);

        $response->assertRedirect();
        foreach ($ids as $id) {
            $this->assertSoftDeleted('posts', ['id' => (int) $id]);
        }
    }
}