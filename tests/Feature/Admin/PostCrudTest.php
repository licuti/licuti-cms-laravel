<?php

namespace Tests\Feature\Admin;

use App\Models\Post;
use App\Models\User;
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
                    'slug' => 'bai-viet-moi',
                    'content' => 'Nội dung test',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $payload);

        $response->assertRedirect(route('admin.posts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('post_translations', [
            'slug' => 'bai-viet-moi',
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
            'title' => 'Cũ',
            'slug' => 'cu',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.posts.update', $post->uuid), [
                'status' => 'published',
                'translations' => [
                    'vi' => [
                        'title' => 'Mới',
                        'slug' => 'moi',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.posts.index'));

        $this->assertDatabaseHas('post_translations', [
            'post_id' => $post->id,
            'title' => 'Mới',
            'slug' => 'moi',
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
            'title' => 'Bai viet mau',
            'slug' => 'bai-viet-mau',
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
    public function updating_a_translation_preserves_other_locales(): void
    {
        $post = Post::factory()->create();
        $post->translations()->create(['locale' => 'vi', 'title' => 'Tiêu đề VI', 'slug' => 'tieu-de-vi']);
        $post->translations()->create(['locale' => 'en', 'title' => 'Old EN', 'slug' => 'old-en']);

        $response = $this->actingAs($this->admin)->put(
            route('admin.posts.update', ['uuid' => $post->uuid, 'lang' => 'en']),
            [
                'status' => 'published',
                'translations' => [
                    'en' => ['title' => 'New EN', 'slug' => 'new-en', 'content' => 'Body'],
                ],
            ]
        );

        $response->assertRedirect(route('admin.posts.index'));
        $this->assertDatabaseHas('post_translations', [
            'post_id' => $post->id,
            'locale' => 'en',
            'title' => 'New EN',
            'slug' => 'new-en',
        ]);
        $this->assertDatabaseHas('post_translations', [
            'post_id' => $post->id,
            'locale' => 'vi',
            'title' => 'Tiêu đề VI',
            'slug' => 'tieu-de-vi',
        ]);
        $this->assertDatabaseCount('post_translations', 2);
    }

    /** @test */
    public function updating_a_translation_requires_title_for_the_current_locale_only(): void
    {
        $post = Post::factory()->create();
        $post->translations()->create(['locale' => 'vi', 'title' => 'VI', 'slug' => 'vi']);

        $response = $this->actingAs($this->admin)->put(
            route('admin.posts.update', ['uuid' => $post->uuid, 'lang' => 'en']),
            ['status' => 'draft', 'translations' => ['en' => ['title' => '', 'slug' => '']]]
        );

        $response->assertSessionHasErrors('translations.en.title');
        $this->assertFalse(session('errors')->has('translations.vi.title'));
    }

    /** @test */
    public function edit_form_renders_only_the_current_language_panel(): void
    {
        $post = Post::factory()->create();
        $post->translations()->create(['locale' => 'vi', 'title' => 'VI', 'slug' => 'vi']);
        $post->translations()->create(['locale' => 'en', 'title' => 'EN', 'slug' => 'en']);

        $response = $this->actingAs($this->admin)->get(
            route('admin.posts.edit', ['uuid' => $post->uuid, 'lang' => 'en'])
        );

        $response->assertOk();
        $response->assertSee('data-lang-panel="en"', false);
        $response->assertDontSee('data-lang-panel="vi"', false);
        $response->assertSee('data-unsaved-guard', false);
        $response->assertSee('data-lang-switch', false);
        $response->assertSee('value="EN"', false);
    }

    /** @test */
    public function create_form_does_not_offer_translation_links_before_saving(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.posts.create'));

        $response->assertOk();
        $response->assertSee('data-lang-panel="vi"', false);
        $response->assertDontSee('Bản dịch khác');
        $response->assertSee('Sau khi lưu, bạn có thể thêm bản dịch', false);
    }

    /** @test */
    public function save_and_edit_keeps_the_edited_language(): void
    {
        $post = Post::factory()->create();
        $post->translations()->create(['locale' => 'vi', 'title' => 'VI', 'slug' => 'vi']);

        $response = $this->actingAs($this->admin)->put(
            route('admin.posts.update', ['uuid' => $post->uuid, 'lang' => 'en']),
            [
                'status' => 'draft',
                'submit_action' => 'save_and_edit',
                'translations' => ['en' => ['title' => 'EN new']],
            ]
        );

        $response->assertRedirect(route('admin.posts.edit', ['uuid' => $post->uuid, 'lang' => 'en']));
    }

    /** @test */
    public function bulk_delete_works(): void
    {
        $posts = Post::factory()->count(3)->create();
        $ids = $posts->pluck('id')->map(fn ($id) => (string) $id)->toArray();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.bulk'), [
                'bulk_module' => 'posts',
                'action' => 'delete',
                'ids' => $ids,
            ]);

        $response->assertRedirect();
        foreach ($ids as $id) {
            $this->assertSoftDeleted('posts', ['id' => (int) $id]);
        }
    }
}
