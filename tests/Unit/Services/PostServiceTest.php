<?php

namespace Tests\Unit\Services;

use App\Core\Enums\ContentStatus;
use App\DTOs\Post\PostDTO;
use App\Models\Language;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\User;
use App\Services\Admin\Post\PostService;
use Database\Factories\PostCategoryFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLanguages;
use Tests\TestCase;

/**
 * Test cho PostService — lớp business logic chính của module Post.
 *
 * Bao phủ:
 *  - Tạo bài viết (create + handleTransaction + sync categories + save translations)
 *  - Cập nhật bài viết
 *  - Xóa bài viết
 *  - Sinh slug unique tự động thêm suffix khi trùng
 *  - Lưu SEO translations
 *  - Phương thức getTabs và getStatusOptions
 */
class PostServiceTest extends TestCase
{
    use RefreshDatabase, WithLanguages;

    private PostService $service;
    private User $author;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpLanguages();
        $this->service = app(PostService::class);
        $this->author = User::factory()->admin()->create();
    }

    /** @test */
    public function it_creates_post_with_translations_and_categories(): void
    {
        $categories = PostCategoryFactory::new()->count(2)->create();

        $dto = PostDTO::fromArray([
            'status'      => ContentStatus::PUBLISHED->value,
            'author_id'   => $this->author->id,
            'category_ids' => $categories->pluck('id')->toArray(),
            'translations' => [
                'vi' => ['title' => 'Bài viết đầu tiên', 'slug' => null],
                'en' => ['title' => 'First Post',         'slug' => 'first-post'],
            ],
        ]);

        $post = $this->service->create($dto);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertNotEmpty($post->uuid);
        $this->assertEquals(ContentStatus::PUBLISHED->value, $post->status);
        $this->assertEquals($this->author->id, $post->author_id);

        // Translations saved
        $this->assertCount(2, $post->translations);
        $this->assertEquals('bai-viet-dau-tien', $post->translate('vi')->slug);
        $this->assertEquals('first-post', $post->translate('en')->slug);

        // Categories synced
        $this->assertCount(2, $post->categories);
    }

    /** @test */
    public function it_generates_unique_slug_with_suffix_when_duplicate(): void
    {
        // Tạo bài viết đầu tiên với slug thủ công
        $this->service->create(PostDTO::fromArray([
            'status'       => ContentStatus::PUBLISHED->value,
            'author_id'    => $this->author->id,
            'translations' => [
                'vi' => ['title' => 'Trùng slug', 'slug' => 'trung-slug'],
            ],
        ]));

        // Tạo bài viết thứ hai với cùng slug
        $second = $this->service->create(PostDTO::fromArray([
            'status'       => ContentStatus::PUBLISHED->value,
            'author_id'    => $this->author->id,
            'translations' => [
                'vi' => ['title' => 'Trùng slug khác', 'slug' => 'trung-slug'],
            ],
        ]));

        $this->assertEquals('trung-slug-1', $second->translate('vi')->slug);
    }

    /** @test */
    public function it_auto_generates_slug_from_title_when_null(): void
    {
        $post = $this->service->create(PostDTO::fromArray([
            'status'       => ContentStatus::DRAFT->value,
            'author_id'    => $this->author->id,
            'translations' => [
                'vi' => ['title' => 'Tiêu đề có Dấu và Khoảng Trắng', 'slug' => null],
            ],
        ]));

        $this->assertEquals('tieu-de-co-dau-va-khoang-trang', $post->translate('vi')->slug);
    }

    /** @test */
    public function it_uses_current_user_as_author_when_null(): void
    {
        $this->actingAs($this->author);

        $post = $this->service->create(PostDTO::fromArray([
            'status'       => ContentStatus::DRAFT->value,
            'author_id'    => null,
            'translations' => [
                'vi' => ['title' => 'Bài viết không có author'],
            ],
        ]));

        $this->assertEquals($this->author->id, $post->author_id);
    }

    /** @test */
    public function it_updates_post_with_new_translations_and_categories(): void
    {
        $original = $this->service->create(PostDTO::fromArray([
            'status'       => ContentStatus::DRAFT->value,
            'author_id'    => $this->author->id,
            'translations' => ['vi' => ['title' => 'Bản gốc']],
        ]));

        $newCategory = PostCategoryFactory::new()->create();

        $updated = $this->service->update($original->uuid, PostDTO::fromArray([
            'status'       => ContentStatus::PUBLISHED->value,
            'author_id'    => $this->author->id,
            'category_ids' => [$newCategory->id],
            'translations' => ['vi' => ['title' => 'Bản cập nhật', 'slug' => 'ban-cap-nhat']],
        ]));

        $this->assertEquals(ContentStatus::PUBLISHED->value, $updated->status);
        $this->assertEquals('Bản cập nhật', $updated->translate('vi')->title);
        $this->assertEquals('ban-cap-nhat', $updated->translate('vi')->slug);
        $this->assertCount(1, $updated->categories);
        $this->assertEquals($newCategory->id, $updated->categories->first()->id);
    }

    /** @test */
    public function it_deletes_post(): void
    {
        $post = Post::factory()->create();

        $result = $this->service->delete($post->uuid);

        $this->assertTrue($result);
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    /** @test */
    public function it_saves_seo_translations(): void
    {
        $post = $this->service->create(PostDTO::fromArray([
            'status'       => ContentStatus::PUBLISHED->value,
            'author_id'    => $this->author->id,
            'translations' => [
                'vi' => [
                    'title'           => 'Bài SEO',
                    'meta_title'       => 'Tiêu đề SEO',
                    'meta_description' => 'Mô tả SEO ngắn',
                    'meta_keywords'    => 'từ khóa, seo',
                ],
            ],
        ]));

        $seo = $post->seoForLocale('vi');

        $this->assertEquals('Tiêu đề SEO', $seo->meta_title);
        $this->assertEquals('Mô tả SEO ngắn', $seo->meta_description);
        $this->assertEquals('từ khóa, seo', $seo->meta_keywords);
    }

    /** @test */
    public function it_returns_status_options_from_enum(): void
    {
        $options = $this->service->getStatusOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('draft', $options);
        $this->assertArrayHasKey('published', $options);
        $this->assertArrayHasKey('archived', $options);
        $this->assertEquals('Bản nháp', $options['draft']);
        $this->assertEquals('Đã xuất bản', $options['published']);
        $this->assertEquals('Lưu trữ', $options['archived']);
    }

    /** @test */
    public function it_returns_tabs_with_counts(): void
    {
        Post::factory()->count(3)->published()->create(['author_id' => $this->author->id]);
        Post::factory()->count(2)->draft()->create(['author_id' => $this->author->id]);
        Post::factory()->count(1)->archived()->create(['author_id' => $this->author->id]);

        $tabs = $this->service->getTabs();

        $this->assertCount(4, $tabs); // 'all' + 3 statuses
        $this->assertEquals('all', $tabs[0]['key']);
        $this->assertEquals(6, $tabs[0]['count']); // tổng tất cả
        $this->assertEquals(3, $tabs[1]['count']); // published
        $this->assertEquals(2, $tabs[2]['count']); // draft
        $this->assertEquals(1, $tabs[3]['count']); // archived
    }

    /** @test */
    public function it_skips_translations_without_title(): void
    {
        $post = $this->service->create(PostDTO::fromArray([
            'status'       => ContentStatus::PUBLISHED->value,
            'author_id'    => $this->author->id,
            'translations' => [
                'vi' => ['title' => 'Có tiêu đề'],
                'en' => ['title' => '', 'content' => 'Không có title'],
            ],
        ]));

        $this->assertCount(1, $post->translations);
        $this->assertEquals('vi', $post->translations->first()->locale);
    }
}