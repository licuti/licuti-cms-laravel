<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandCrudTest extends TestCase
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

    public function test_guest_cannot_access_admin_brands_index(): void
    {
        $response = $this->get(route('admin.brands.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_brands_index(): void
    {
        $brand = Brand::create(['website' => 'https://apple.com', 'is_active' => true]);
        $brand->translations()->create([
            'locale' => 'vi',
            'name'   => 'Apple',
            'slug'   => 'apple',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.brands.index'));

        $response->assertStatus(200);
        $response->assertSee('Apple');
    }

    public function test_admin_can_create_brand(): void
    {
        $payload = [
            'website'       => 'https://samsung.com',
            'is_active'     => 1,
            'display_order' => 2,
            'translations'  => [
                'vi' => [
                    'name'        => 'Samsung',
                    'slug'        => 'samsung',
                    'description' => 'Thương hiệu công nghệ Hàn Quốc',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.brands.store'), $payload);

        $response->assertRedirect(route('admin.brands.index'));
        $this->assertDatabaseHas('brand_translations', [
            'name' => 'Samsung',
            'slug' => 'samsung',
        ]);
    }

    public function test_admin_can_update_brand(): void
    {
        $brand = Brand::create(['website' => 'https://sony.com', 'is_active' => true]);
        $brand->translations()->create([
            'locale' => 'vi',
            'name'   => 'Sony',
            'slug'   => 'sony',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.brands.update', $brand->uuid), [
            'website'      => 'https://sony.com.vn',
            'is_active'    => 1,
            'translations' => [
                'vi' => [
                    'name' => 'Sony Corporation',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.brands.index'));
        $this->assertDatabaseHas('brand_translations', [
            'brand_id' => $brand->id,
            'name'     => 'Sony Corporation',
        ]);
    }

    public function test_admin_can_delete_brand(): void
    {
        $brand = Brand::create(['is_active' => true]);
        $brand->translations()->create([
            'locale' => 'vi',
            'name'   => 'Thương hiệu thử nghiệm',
            'slug'   => 'thuong-hieu-thu-nghiem',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.brands.destroy', $brand->uuid));

        $response->assertRedirect(route('admin.brands.index'));
        $this->assertSoftDeleted('brands', ['id' => $brand->id]);
    }
}
