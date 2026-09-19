<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuCrudTest extends TestCase
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

    public function test_guest_cannot_access_menus_index(): void
    {
        $response = $this->get(route('admin.menus.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_menus_index(): void
    {
        $menu = Menu::create([
            'name'      => 'Menu chính',
            'slug'      => 'main-menu',
            'location'  => 'header',
            'is_active' => true,
        ]);
        $menu->items()->create([
            'title'         => 'Trang chủ',
            'url'           => '/',
            'display_order' => 0,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.menus.index'));

        $response->assertStatus(200);
        $response->assertSee('Menu chính');
        $response->assertSee('main-menu');
    }

    public function test_admin_can_create_menu(): void
    {
        $payload = [
            'name'      => 'Menu Chân Trang',
            'slug'      => 'footer-menu',
            'location'  => 'footer',
            'is_active' => 1,
            'items'     => [
                ['title' => 'Chính sách', 'url' => '/policy', 'target' => '_self', 'display_order' => 0],
                ['title' => 'Điều khoản', 'url' => '/terms', 'target' => '_self', 'display_order' => 1],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.menus.store'), $payload);

        $response->assertRedirect(route('admin.menus.index'));
        $this->assertDatabaseHas('menus', ['name' => 'Menu Chân Trang', 'slug' => 'footer-menu', 'location' => 'footer']);
        $this->assertDatabaseHas('menu_items', ['title' => 'Chính sách', 'url' => '/policy']);
        $this->assertDatabaseHas('menu_items', ['title' => 'Điều khoản', 'url' => '/terms']);
    }

    public function test_admin_can_update_menu(): void
    {
        $menu = Menu::create([
            'name'      => 'Menu Cũ',
            'slug'      => 'menu-cu',
            'location'  => 'sidebar',
            'is_active' => true,
        ]);
        $menu->items()->create([
            'title' => 'Mục cũ',
            'url'   => '/old',
        ]);

        $payload = [
            'name'      => 'Menu Đã Đổi Tên',
            'slug'      => 'menu-moi',
            'location'  => 'header',
            'is_active' => 1,
            'items'     => [
                ['title' => 'Mục mới', 'url' => '/new', 'display_order' => 1],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.menus.update', $menu->uuid), $payload);

        $response->assertRedirect(route('admin.menus.index'));
        $this->assertDatabaseHas('menus', ['name' => 'Menu Đã Đổi Tên', 'location' => 'header']);
        $this->assertDatabaseHas('menu_items', ['title' => 'Mục mới', 'url' => '/new']);
    }

    public function test_admin_can_delete_menu(): void
    {
        $menu = Menu::create([
            'name' => 'Menu Tạm',
            'slug' => 'menu-tam',
        ]);
        $item = $menu->items()->create([
            'title' => 'Liên kết tạm',
            'url'   => '/tam',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.menus.destroy', $menu->uuid));

        $response->assertRedirect(route('admin.menus.index'));
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
    }
}
