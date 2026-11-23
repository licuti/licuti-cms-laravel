<?php

namespace Tests\Feature\Admin;

use App\Models\Media;
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
    public function admin_can_save_post_image(): void
    {
        $media = Media::create([
            'disk' => 'public',
            'file_path' => 'media/post.jpg',
            'file_name' => 'post.jpg',
            'original_name' => 'post.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'status' => 'draft',
            'image_media_id' => $media->id,
            'translations' => ['vi' => ['title' => 'Bài có ảnh']],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', ['image' => $media->id]);
    }

    /** @test */
    public function updating_post_without_image_field_preserves_existing_media(): void
    {
        $media = Media::create([
            'disk' => 'public',
            'file_path' => 'media/existing.jpg',
            'file_name' => 'existing.jpg',
            'original_name' => 'existing.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1000,
        ]);
        $post = Post::factory()->create(['image' => $media->id]);
        $post->translations()->create(['locale' => 'vi', 'title' => 'Cũ', 'slug' => 'cu']);

        $this->actingAs($this->admin)->put(route('admin.posts.update', $post->uuid), [
            'status' => 'draft',
            'translations' => ['vi' => ['title' => 'Mới']],
        ])->assertRedirect();

        $this->assertSame($media->id, $post->fresh()->image);
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

    /** @test */
    public function view_count_is_saved_on_create_and_update(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'status' => 'draft',
            'view_count' => 120,
            'translations' => ['vi' => ['title' => 'Bài có lượt xem']],
        ]);
        $response->assertRedirect();

        $post = Post::first();
        $this->assertSame(120, $post->view_count);

        $this->actingAs($this->admin)->put(route('admin.posts.update', $post->uuid), [
            'status' => 'draft',
            'view_count' => 500,
            'translations' => ['vi' => ['title' => 'Bài có lượt xem']],
        ])->assertRedirect();

        $this->assertSame(500, $post->fresh()->view_count);
    }

    /** @test */
    public function update_without_view_count_keeps_existing_value(): void
    {
        $post = Post::factory()->create(['view_count' => 42]);
        $post->translations()->create(['locale' => 'vi', 'title' => 'VI', 'slug' => 'vi']);

        $this->actingAs($this->admin)->put(route('admin.posts.update', $post->uuid), [
            'status' => 'draft',
            'translations' => ['vi' => ['title' => 'VI mới']],
        ])->assertRedirect();

        $this->assertSame(42, $post->fresh()->view_count);
    }

    /** @test */
    public function negative_view_count_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'status' => 'draft',
            'view_count' => -5,
            'translations' => ['vi' => ['title' => 'Test']],
        ]);

        $response->assertSessionHasErrors('view_count');
        $this->assertDatabaseCount('posts', 0);
    }

    /** @test */
    public function og_image_uses_url_mode_of_image_upload_component(): void
    {
        $post = Post::factory()->create();
        $post->translations()->create(['locale' => 'vi', 'title' => 'VI', 'slug' => 'vi']);

        $response = $this->actingAs($this->admin)->get(route('admin.posts.edit', $post->uuid));

        $response->assertOk();
        $response->assertSee('name="translations[vi][og_image]"', false);
        $response->assertDontSee('og_image_uuid', false);
        $response->assertDontSee('seo-og-image-btn', false);
        $response->assertSee('name="translations[vi][focus_keyword]"', false);
    }

    /** @test */
    public function seo_social_and_advanced_fields_are_validated(): void
    {
        $post = fn (array $seo) => $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'status' => 'draft',
            'translations' => ['vi' => array_merge(['title' => 'Bài SEO'], $seo)],
        ]);

        $post(['canonical_url' => 'khong-phai-url'])->assertSessionHasErrors('translations.vi.canonical_url');
        $post(['schema_type' => 'BogusType'])->assertSessionHasErrors('translations.vi.schema_type');
        $post(['robots_index' => 'abc'])->assertSessionHasErrors('translations.vi.robots_index');
        $post(['robots_follow' => 'xyz'])->assertSessionHasErrors('translations.vi.robots_follow');
        $post(['og_title' => str_repeat('a', 256)])->assertSessionHasErrors('translations.vi.og_title');
        $post(['og_description' => str_repeat('a', 501)])->assertSessionHasErrors('translations.vi.og_description');
    }

    /** @test */
    public function focus_keyword_is_saved(): void
    {
        $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'status' => 'draft',
            'translations' => ['vi' => ['title' => 'Bài focus keyword', 'focus_keyword' => 'tối ưu seo']],
        ])->assertRedirect();

        $this->assertDatabaseHas('seo_metadata', [
            'locale' => 'vi',
            'focus_keyword' => 'tối ưu seo',
        ]);
    }

    /** @test */
    public function og_image_url_is_saved_and_invalid_url_is_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'status' => 'draft',
            'translations' => [
                'vi' => ['title' => 'Bài có OG image', 'og_image' => 'https://cdn.example.test/og.jpg'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('seo_metadata', [
            'locale' => 'vi',
            'og_image' => 'https://cdn.example.test/og.jpg',
        ]);

        $this->actingAs($this->admin)->post(route('admin.posts.store'), [
            'status' => 'draft',
            'translations' => [
                'vi' => ['title' => 'Bài OG lỗi', 'og_image' => 'khong-phai-url'],
            ],
        ])->assertSessionHasErrors('translations.vi.og_image');
    }
}
