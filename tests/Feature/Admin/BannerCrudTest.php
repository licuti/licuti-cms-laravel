<?php

namespace Tests\Feature\Admin;

use App\Models\Banner;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);

        Language::create([
            'code'        => 'vi',
            'name'        => 'Tiếng Việt',
            'native_name' => 'Tiếng Việt',
            'is_default'  => true,
            'is_active'   => true,
        ]);
    }

    public function test_guest_cannot_access_banners_index(): void
    {
        $response = $this->get(route('admin.banners.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_banners_index(): void
    {
        $banner = Banner::create([
            'position'      => 'home_slider',
            'link'          => 'https://example.com/summer-sale',
            'target'        => '_blank',
            'display_order' => 1,
            'is_active'     => true,
        ]);
        $banner->translations()->create([
            'locale' => 'vi',
            'title'  => 'Khuyến mãi hè',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.banners.index'));

        $response->assertStatus(200);
        $response->assertSee('Khuyến mãi hè');
        $response->assertSee('https://example.com/summer-sale');
    }

    public function test_admin_can_create_banner(): void
    {
        $payload = [
            'position'      => 'home_slider',
            'link'          => 'https://example.com/new-season',
            'target'        => '_self',
            'display_order' => 2,
            'is_active'     => 1,
            'translations'  => [
                'vi' => [
                    'title'       => 'Bộ sưu tập Thu Đông',
                    'description' => 'Mô tả bộ sưu tập mới',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.banners.store'), $payload);

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertDatabaseHas('banners', ['position' => 'home_slider', 'link' => 'https://example.com/new-season']);
        $this->assertDatabaseHas('banner_translations', ['title' => 'Bộ sưu tập Thu Đông']);
    }

    public function test_admin_can_update_banner(): void
    {
        $banner = Banner::create([
            'position'  => 'sidebar',
            'is_active' => true,
        ]);
        $banner->translations()->create([
            'locale' => 'vi',
            'title'  => 'Tiêu đề cũ',
        ]);

        $payload = [
            'position'      => 'sidebar',
            'link'          => 'https://example.com/updated',
            'target'        => '_blank',
            'display_order' => 3,
            'is_active'     => 1,
            'translations'  => [
                'vi' => [
                    'title'       => 'Tiêu đề mới đã sửa',
                    'description' => 'Mô tả mới',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.banners.update', $banner->uuid), $payload);

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertDatabaseHas('banners', ['link' => 'https://example.com/updated', 'target' => '_blank']);
        $this->assertDatabaseHas('banner_translations', ['title' => 'Tiêu đề mới đã sửa']);
    }

    public function test_admin_can_delete_banner(): void
    {
        $banner = Banner::create([
            'position' => 'popup',
        ]);
        $banner->translations()->create([
            'locale' => 'vi',
            'title'  => 'Banner cần xóa',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.banners.destroy', $banner->uuid));

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertSoftDeleted('banners', ['id' => $banner->id]);
    }
}
