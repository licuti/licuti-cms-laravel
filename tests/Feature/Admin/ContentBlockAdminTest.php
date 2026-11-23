<?php

namespace Tests\Feature\Admin;

use App\Models\ContentBlock;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithLanguages;
use Tests\TestCase;

class ContentBlockAdminTest extends TestCase
{
    use RefreshDatabase, WithLanguages;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpLanguages();
        $this->seed(RolePermissionSeeder::class);
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_content_blocks_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.content-blocks.index'))
            ->assertOk()
            ->assertSee('Danh sách Khối nội dung');
    }

    public function test_admin_can_create_multilingual_content_block_and_get_stable_shortcode(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.content-blocks.store'), [
            'status' => 'published',
            'translations' => [
                'vi' => [
                    'title' => 'Khối giới thiệu',
                    'slug' => 'khoi-gioi-thieu',
                    'excerpt' => 'Mô tả khối',
                    'content' => '<section><strong>Xin chào</strong></section>',
                ],
                'en' => [
                    'title' => 'Introduction block',
                    'slug' => 'introduction-block',
                    'content' => '<section>Hello</section>',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.content-blocks.index'));
        $block = ContentBlock::query()->with('translations')->firstOrFail();

        $this->assertSame('[content_block id="'.$block->uuid.'"]', $block->shortcode);
        $this->assertCount(2, $block->translations);
        $this->assertDatabaseHas('content_block_translations', [
            'content_block_id' => $block->id,
            'locale' => 'vi',
            'content' => '<section><strong>Xin chào</strong></section>',
        ]);
    }

    public function test_admin_can_update_only_current_translation(): void
    {
        $block = ContentBlock::query()->create(['status' => 'draft']);
        $block->translations()->createMany([
            ['locale' => 'vi', 'title' => 'Tiếng Việt', 'slug' => 'tieng-viet', 'content' => 'VI'],
            ['locale' => 'en', 'title' => 'English', 'slug' => 'english', 'content' => 'EN'],
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.content-blocks.update', ['uuid' => $block->uuid, 'lang' => 'en']), [
                'status' => 'draft',
                'translations' => [
                    'en' => ['title' => 'Updated English', 'content' => 'Updated'],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('content_block_translations', [
            'content_block_id' => $block->id,
            'locale' => 'en',
            'title' => 'Updated English',
        ]);
        $this->assertDatabaseHas('content_block_translations', [
            'content_block_id' => $block->id,
            'locale' => 'vi',
            'title' => 'Tiếng Việt',
        ]);
    }

    public function test_user_without_content_block_permission_is_forbidden(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.access');

        $this->actingAs($user)
            ->get(route('admin.content-blocks.index'))
            ->assertForbidden();
    }
}
