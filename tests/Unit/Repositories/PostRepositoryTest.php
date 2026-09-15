<?php

namespace Tests\Unit\Repositories;

use App\Models\Language;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Eloquent\PostRepository;
use Database\Factories\PostCategoryFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLanguages;
use Tests\TestCase;

/**
 * Test cho PostRepository — lớp truy vấn DB, filter, eager loading.
 *
 * Bao phủ:
 *  - getFiltered với các filter (tab, category, keyword)
 *  - Eager loading không bị N+1
 *  - deleteByIds (bulk delete)
 *  - updateStatusByIds (bulk update)
 *  - countByStatus
 *  - findByUuid không tồn tại → exception
 */
class PostRepositoryTest extends TestCase
{
    use RefreshDatabase, WithLanguages;

    private PostRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpLanguages();
        $this->repo = app(PostRepository::class);
    }

    /** @test */
    public function it_filters_by_tab_status(): void
    {
        Post::factory()->count(3)->published()->create();
        Post::factory()->count(2)->draft()->create();

        $published = $this->repo->getFiltered(['tab' => 'published']);
        $draft = $this->repo->getFiltered(['tab' => 'draft']);
        $all = $this->repo->getFiltered(['tab' => 'all']);

        $this->assertEquals(3, $published->total());
        $this->assertEquals(2, $draft->total());
        $this->assertEquals(5, $all->total());
    }

    /** @test */
    public function it_filters_by_category(): void
    {
        $cat1 = PostCategoryFactory::new()->create();
        $cat2 = PostCategoryFactory::new()->create();

        $postInCat1 = Post::factory()->create();
        $postInCat1->categories()->attach($cat1->id);

        $postInCat2 = Post::factory()->create();
        $postInCat2->categories()->attach($cat2->id);

        $result = $this->repo->getFiltered(['category' => $cat1->id]);

        $this->assertEquals(1, $result->total());
        $this->assertEquals($postInCat1->id, $result->first()->id);
    }

    /** @test */
    public function it_searches_by_keyword_in_translations(): void
    {
        $matching = Post::factory()->create();
        $matching->translations()->create([
            'locale' => 'vi',
            'title'  => 'Laravel Framework Hướng dẫn',
            'slug'   => 'laravel-framework',
        ]);

        $other = Post::factory()->create();
        $other->translations()->create([
            'locale' => 'vi',
            'title'  => 'Bài viết khác',
            'slug'   => 'bai-viet-khac',
        ]);

        $result = $this->repo->getFiltered(['keyword' => 'Laravel']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals($matching->id, $result->first()->id);
    }

    /** @test */
    public function it_eager_loads_relations_to_prevent_n_plus_1(): void
    {
        Post::factory()->count(3)->withVietnamese()->create();

        // Query + đếm query log
        \DB::enableQueryLog();
        $this->repo->getFiltered([]);
        $queries = \DB::getQueryLog();
        \DB::disableQueryLog();

        // Không được query translations / author / categories / imageMedia
        // trong vòng lặp (mỗi row) → chỉ có query duy nhất với các JOIN.
        // Tổng queries cho 3 rows + relations nên <= ~5 queries (chứ không phải 3 * 4 = 12)
        $this->assertLessThanOrEqual(6, count($queries), 'Có thể đang bị N+1 query');
    }

    /** @test */
    public function it_respects_per_page_filter(): void
    {
        Post::factory()->count(20)->create();

        $result = $this->repo->getFiltered(['per_page' => 5]);

        $this->assertEquals(5, $result->perPage());
        $this->assertEquals(20, $result->total());

        // Kiểm tra giới hạn max 100
        $capped = $this->repo->getFiltered(['per_page' => 999]);
        $this->assertEquals(100, $capped->perPage()); // bị cap tại 100

        // Kiểm tra giới hạn min 5
        $minCapped = $this->repo->getFiltered(['per_page' => 1]);
        $this->assertEquals(5, $minCapped->perPage());

        // Default 15 khi không truyền
        $default = $this->repo->getFiltered([]);
        $this->assertEquals(15, $default->perPage());
    }

    /** @test */
    public function it_bulk_deletes_by_ids(): void
    {
        $posts = Post::factory()->count(3)->create();
        $ids = $posts->pluck('id')->toArray();

        $deleted = $this->repo->deleteByIds($ids);

        $this->assertEquals(3, $deleted);
        $this->assertSoftDeleted('posts', ['id' => $ids[0]]);
    }

    /** @test */
    public function it_bulk_updates_status_by_ids(): void
    {
        $posts = Post::factory()->count(3)->draft()->create();

        $count = $this->repo->updateStatusByIds(
            $posts->pluck('id')->toArray(),
            'published'
        );

        $this->assertEquals(3, $count);
        foreach ($posts as $post) {
            $this->assertDatabaseHas('posts', ['id' => $post->id, 'status' => 'published']);
        }
    }

    /** @test */
    public function it_counts_by_status(): void
    {
        Post::factory()->count(3)->published()->create();
        Post::factory()->count(2)->draft()->create();
        Post::factory()->count(1)->archived()->create();

        $counts = $this->repo->countByStatus();

        $this->assertEquals(3, $counts['published']);
        $this->assertEquals(2, $counts['draft']);
        $this->assertEquals(1, $counts['archived']);
    }

    /** @test */
    public function it_finds_by_uuid(): void
    {
        $post = Post::factory()->create();

        $found = $this->repo->findByUuid($post->uuid);

        $this->assertNotNull($found);
        $this->assertEquals($post->id, $found->id);
    }

    /** @test */
    public function it_throws_exception_when_uuid_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->repo->findByUuid('non-existent-uuid');
    }
}