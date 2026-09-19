<?php

namespace Tests\Feature\Admin;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_guest_cannot_access_admin_tags_index(): void
    {
        $response = $this->get(route('admin.tags.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_tags_index(): void
    {
        Tag::create(['name' => 'Công nghệ', 'slug' => 'cong-nghe']);

        $response = $this->actingAs($this->admin)->get(route('admin.tags.index'));

        $response->assertStatus(200);
        $response->assertSee('Công nghệ');
    }

    public function test_admin_can_create_tag(): void
    {
        $payload = [
            'name'          => 'Khuyến mãi hot',
            'display_order' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.tags.store'), $payload);

        $response->assertRedirect(route('admin.tags.index'));
        $this->assertDatabaseHas('tags', [
            'name' => 'Khuyến mãi hot',
            'slug' => 'khuyen-mai-hot',
        ]);
    }

    public function test_admin_can_update_tag(): void
    {
        $tag = Tag::create(['name' => 'Tin tức cũ', 'slug' => 'tin-tuc-cu']);

        $response = $this->actingAs($this->admin)->put(route('admin.tags.update', $tag->uuid), [
            'name' => 'Tin tức mới',
        ]);

        $response->assertRedirect(route('admin.tags.index'));
        $this->assertDatabaseHas('tags', [
            'id'   => $tag->id,
            'name' => 'Tin tức mới',
        ]);
    }

    public function test_admin_can_delete_tag(): void
    {
        $tag = Tag::create(['name' => 'Tag cần xóa', 'slug' => 'tag-can-xoa']);

        $response = $this->actingAs($this->admin)->delete(route('admin.tags.destroy', $tag->uuid));

        $response->assertRedirect(route('admin.tags.index'));
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
