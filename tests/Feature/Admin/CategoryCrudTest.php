<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
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

    public function test_guest_cannot_access_admin_categories_index(): void
    {
        $response = $this->get(route('admin.categories.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_categories_index(): void
    {
        $category = Category::create(['is_active' => true, 'display_order' => 1]);
        $category->translations()->create(['locale' => 'vi', 'name' => 'Điện thoại', 'slug' => 'dien-thoai']);

        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Điện thoại');
    }

    public function test_admin_can_view_categories_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.categories.create'));

        $response->assertStatus(200);
        $response->assertSee('translations[vi][name]');
    }

    public function test_admin_can_create_category_with_default_locale_only(): void
    {
        // Chỉ điền ngôn ngữ mặc định (vi) — các ngôn ngữ khác để trống vẫn hợp lệ
        $payload = [
            'display_order' => 2,
            'status'        => '1',
            'translations'  => [
                'vi' => [
                    'name'        => 'Laptop',
                    'slug'        => 'laptop',
                    'description' => 'Máy tính xách tay',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), $payload);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['display_order' => 2, 'is_active' => true]);
        $this->assertDatabaseHas('category_translations', [
            'locale' => 'vi',
            'name'   => 'Laptop',
            'slug'   => 'laptop',
        ]);
    }

    public function test_create_category_requires_default_locale_name(): void
    {
        $payload = [
            'translations' => [
                'vi'  => ['name' => ''],
                'en'  => ['name' => 'Phones'],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), $payload);

        $response->assertSessionHasErrors('translations.vi.name');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_admin_can_create_child_category(): void
    {
        $parent = Category::create(['is_active' => true]);
        $parent->translations()->create(['locale' => 'vi', 'name' => 'Điện thoại', 'slug' => 'dien-thoai']);

        $payload = [
            'parent_id'    => $parent->id,
            'translations' => ['vi' => ['name' => 'iPhone']],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), $payload);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['parent_id' => $parent->id]);
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::create(['is_active' => true, 'display_order' => 5]);
        $category->translations()->create(['locale' => 'vi', 'name' => 'Tên cũ', 'slug' => 'ten-cu']);

        $payload = [
            'display_order' => 9,
            'translations'  => ['vi' => ['name' => 'Tên mới', 'slug' => 'ten-moi']],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category->uuid), $payload);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'display_order' => 9]);
        $this->assertDatabaseHas('category_translations', [
            'category_id' => $category->id,
            'name'        => 'Tên mới',
            'slug'        => 'ten-moi',
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        $category = Category::create(['is_active' => true]);
        $category->translations()->create(['locale' => 'vi', 'name' => 'Tạm xóa', 'slug' => 'tam-xoa']);

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category->uuid));

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_deleting_category_moves_children_up_one_level(): void
    {
        $parent = Category::create(['is_active' => true]);
        $parent->translations()->create(['locale' => 'vi', 'name' => 'Cha', 'slug' => 'cha']);
        $child = Category::create(['is_active' => true, 'parent_id' => $parent->id]);
        $child->translations()->create(['locale' => 'vi', 'name' => 'Con', 'slug' => 'con']);

        $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $parent->uuid));

        $this->assertDatabaseMissing('categories', ['id' => $parent->id]);
        $this->assertDatabaseHas('categories', ['id' => $child->id, 'parent_id' => null]);
    }

    public function test_bulk_toggle_status(): void
    {
        $category = Category::create(['is_active' => true]);
        $category->translations()->create(['locale' => 'vi', 'name' => 'Ẩn', 'slug' => 'an']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.bulk'), ['bulk_module' => 'categories', 'action' => 'status_0', 'ids' => [$category->uuid]]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertFalse((bool) $category->fresh()->is_active);
    }
}
