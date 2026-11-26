<?php

namespace Tests\Feature\Storefront;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLanguages;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, WithLanguages;

    public function test_only_published_posts_are_visible_on_the_storefront(): void
    {
        Post::factory()->published()->withVietnamese('Bài viết đã xuất bản')->create();
        Post::factory()->draft()->withVietnamese('Bản nháp nội bộ')->create();

        $response = $this->get(route('storefront.posts.index'));

        $response->assertOk()
            ->assertSee('Bài viết đã xuất bản')
            ->assertDontSee('Bản nháp nội bộ');
    }

    public function test_published_post_can_be_opened_by_slug_and_increments_views(): void
    {
        $post = Post::factory()->published()->withVietnamese('Bài viết chi tiết')->create([
            'view_count' => 4,
        ]);

        $response = $this->get(route('storefront.posts.show', 'bai-viet-chi-tiet'));

        $response->assertOk()
            ->assertSee('Bài viết chi tiết');
        $this->assertSame(5, $post->fresh()->view_count);
    }

    public function test_draft_post_is_not_accessible_by_slug(): void
    {
        Post::factory()->draft()->withVietnamese('Bản nháp')->create();

        $this->get(route('storefront.posts.show', 'ban-nhap'))
            ->assertNotFound();
    }

    public function test_storefront_applies_theme_layout_and_post_options(): void
    {
        app(\App\Services\Theme\ThemeSettingsService::class)->save('default', [
            'blog_layout' => 'list',
            'grid_columns' => 2,
            'show_reading_time' => true,
            'show_social_share' => true,
            'related_posts_count' => 2,
            'show_announcement' => true,
            'announcement_text' => 'Thông báo thử nghiệm',
        ]);

        Post::factory()->published()->withVietnamese('Bài viết số một')->create();
        Post::factory()->published()->withVietnamese('Bài viết số hai')->create();

        $this->get(route('storefront.posts.index'))
            ->assertOk()
            ->assertSee('post-grid--list')
            ->assertSee('--grid-columns: 2;')
            ->assertSee('phút đọc')
            ->assertSee('Thông báo thử nghiệm');

        $this->get(route('storefront.posts.show', 'bai-viet-so-mot'))
            ->assertOk()
            ->assertSee('CHIA SẺ')
            ->assertSee('Bài viết liên quan')
            ->assertSee('Bài viết số hai');
    }
}
