<?php

namespace Tests\Feature\Admin;

use App\Enums\FlashSaleStatus;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FlashSaleAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $normalUser;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->normalUser = User::factory()->create();

        $this->product = Product::create([
            'sku'             => 'PROD-FS-01',
            'price'           => 200000,
            'is_active'       => true,
            'stock_quantity'  => 50,
            'track_inventory' => true,
        ]);
        $this->product->translations()->create([
            'locale' => 'vi',
            'name'   => 'Áo Thun Cao Cấp',
            'slug'   => 'ao-thun-cao-cap',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.flash-sales.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->normalUser->givePermissionTo('admin.access');
        $response = $this->actingAs($this->normalUser)->get(route('admin.flash-sales.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_flash_sales_index(): void
    {
        $fs = FlashSale::create([
            'starts_at' => Carbon::now()->addHour(),
            'ends_at'   => Carbon::now()->addHours(3),
            'is_active' => true,
        ]);
        $fs->translations()->create([
            'locale' => 'vi',
            'name'   => 'Chiến dịch Khai trương',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.flash-sales.index'));

        $response->assertStatus(200);
        $response->assertSee('Danh sách Flash Sale');
        $response->assertSee('Chiến dịch Khai trương');
        $response->assertSee('Sắp diễn ra');
    }

    public function test_admin_can_view_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.flash-sales.create'));

        $response->assertStatus(200);
        $response->assertSee('Tạo mới Flash Sale');
        $response->assertSee('Áo Thun Cao Cấp');
    }

    public function test_admin_can_create_flash_sale(): void
    {
        $payload = [
            'starts_at'    => Carbon::now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at'      => Carbon::now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
            'is_active'    => 1,
            'translations' => [
                'vi' => [
                    'name'        => 'Flash Sale Giờ Vàng',
                    'description' => 'Giảm sâu trong 2 tiếng',
                ],
            ],
            'products'     => [
                [
                    'product_id'         => $this->product->id,
                    'product_variant_id' => null,
                    'flash_price'        => 120000,
                    'quantity_limit'     => 15,
                    'limit_per_user'     => 2,
                    'sort_order'         => 0,
                ],
            ],
            'submit_action' => 'save',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.flash-sales.store'), $payload);

        $response->assertRedirect(route('admin.flash-sales.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('flash_sales', [
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('flash_sale_translations', [
            'locale' => 'vi',
            'name'   => 'Flash Sale Giờ Vàng',
        ]);
        $this->assertDatabaseHas('flash_sale_products', [
            'product_id'     => $this->product->id,
            'variant_key'    => "{$this->product->id}-0",
            'flash_price'    => 120000,
            'quantity_limit' => 15,
            'limit_per_user' => 2,
        ]);
    }

    public function test_creation_fails_when_flash_price_is_not_lower_than_regular(): void
    {
        $payload = [
            'starts_at'    => Carbon::now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at'      => Carbon::now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
            'is_active'    => 1,
            'translations' => [
                'vi' => ['name' => 'Flash Sale Lỗi Giá'],
            ],
            'products'     => [
                [
                    'product_id'         => $this->product->id,
                    'product_variant_id' => null,
                    'flash_price'        => 250000, // Giá gốc là 200.000đ -> phải nhỏ hơn
                    'quantity_limit'     => 10,
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.flash-sales.store'), $payload);

        $response->assertSessionHasErrors('products.0.flash_price');
    }

    public function test_admin_can_view_edit_form(): void
    {
        $fs = FlashSale::create([
            'starts_at' => Carbon::now()->addHour(),
            'ends_at'   => Carbon::now()->addHours(3),
            'is_active' => true,
        ]);
        $fs->translations()->create([
            'locale' => 'vi',
            'name'   => 'Flash Sale Edit Test',
        ]);
        $fs->products()->create([
            'product_id'     => $this->product->id,
            'variant_key'    => "{$this->product->id}-0",
            'flash_price'    => 150000,
            'quantity_limit' => 20,
            'sold_quantity'  => 0,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.flash-sales.edit', $fs->uuid));

        $response->assertStatus(200);
        $response->assertSee('Flash Sale Edit Test');
        $response->assertSee('150000');
    }

    public function test_admin_can_update_flash_sale(): void
    {
        $fs = FlashSale::create([
            'starts_at' => Carbon::now()->addHour(),
            'ends_at'   => Carbon::now()->addHours(3),
            'is_active' => true,
        ]);
        $fs->translations()->create([
            'locale' => 'vi',
            'name'   => 'Chiến dịch Cũ',
        ]);
        $fs->products()->create([
            'product_id'     => $this->product->id,
            'variant_key'    => "{$this->product->id}-0",
            'flash_price'    => 150000,
            'quantity_limit' => 10,
        ]);

        $updatePayload = [
            'starts_at'    => Carbon::now()->addHours(2)->format('Y-m-d H:i:s'),
            'ends_at'      => Carbon::now()->addHours(5)->format('Y-m-d H:i:s'),
            'is_active'    => 1,
            'translations' => [
                'vi' => ['name' => 'Chiến dịch Mới'],
            ],
            'products'     => [
                [
                    'product_id'         => $this->product->id,
                    'product_variant_id' => null,
                    'flash_price'        => 110000,
                    'quantity_limit'     => 25,
                    'limit_per_user'     => 1,
                    'sort_order'         => 0,
                ],
            ],
            'submit_action' => 'save',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.flash-sales.update', $fs->uuid), $updatePayload);

        $response->assertRedirect(route('admin.flash-sales.index'));
        $this->assertDatabaseHas('flash_sale_translations', [
            'flash_sale_id' => $fs->id,
            'name'          => 'Chiến dịch Mới',
        ]);
        $this->assertDatabaseHas('flash_sale_products', [
            'flash_sale_id'  => $fs->id,
            'flash_price'    => 110000,
            'quantity_limit' => 25,
        ]);
    }

    public function test_admin_can_toggle_flash_sale_status(): void
    {
        $fs = FlashSale::create([
            'starts_at' => Carbon::now()->addHour(),
            'ends_at'   => Carbon::now()->addHours(3),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.flash-sales.toggle-status', $fs->uuid));

        $this->assertDatabaseHas('flash_sales', [
            'id'        => $fs->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_flash_sale(): void
    {
        $fs = FlashSale::create([
            'starts_at' => Carbon::now()->addHours(5),
            'ends_at'   => Carbon::now()->addHours(10),
            'is_active' => true,
        ]);
        $fs->translations()->create([
            'locale' => 'vi',
            'name'   => 'Sắp Xóa',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.flash-sales.destroy', $fs->uuid));

        $response->assertRedirect(route('admin.flash-sales.index'));
        $this->assertSoftDeleted('flash_sales', [
            'id' => $fs->id,
        ]);
    }
}
