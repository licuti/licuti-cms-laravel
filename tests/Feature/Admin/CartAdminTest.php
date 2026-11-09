<?php

namespace Tests\Feature\Admin;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CartAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $unauthorizedUser;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->unauthorizedUser = User::factory()->create();

        $this->product = Product::create([
            'sku'             => 'ADMIN-CART-01',
            'price'           => 120000,
            'is_active'       => true,
            'product_type'    => 'simple',
            'stock_quantity'  => 20,
            'track_inventory' => true,
        ]);
        $this->product->translations()->create(['locale' => 'vi', 'name' => 'Sản phẩm Admin', 'slug' => 'sp-admin']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.carts.index'));
        $response->assertRedirect('/admin/login');
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $this->unauthorizedUser->givePermissionTo('admin.access');
        $response = $this->actingAs($this->unauthorizedUser)->get(route('admin.carts.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_carts_index(): void
    {
        Cart::create(['status' => CartStatus::ACTIVE, 'currency' => 'VND']);
        Cart::create(['status' => CartStatus::ABANDONED, 'currency' => 'VND']);

        $response = $this->actingAs($this->admin)->get(route('admin.carts.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.carts.index');
        $response->assertViewHas('carts');
        $response->assertViewHas('statusCounts');
    }

    public function test_admin_can_filter_carts_by_status(): void
    {
        Cart::create(['status' => CartStatus::ACTIVE]);
        Cart::create(['status' => CartStatus::ABANDONED]);

        $response = $this->actingAs($this->admin)->get(route('admin.carts.index', ['status' => 'abandoned']));

        $response->assertStatus(200);
        $carts = $response->viewData('carts');
        $this->assertCount(1, $carts);
        $this->assertEquals(CartStatus::ABANDONED, $carts->first()->status);
    }

    public function test_admin_can_view_cart_detail(): void
    {
        $this->product->update(['price' => 150000]);

        $cart = Cart::create(['status' => CartStatus::ACTIVE]);
        CartItem::create([
            'cart_id'             => $cart->id,
            'product_id'          => $this->product->id,
            'line_key'            => "p{$this->product->id}-v0",
            'quantity'            => 2,
            'unit_price_snapshot' => 120000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.carts.show', $cart->uuid));

        $response->assertStatus(200);
        $response->assertViewIs('admin.carts.show');
        $response->assertViewHas('cart');
        $response->assertViewHas('summary');
        // Do snapshot 120k khác với giá hiện tại 150k, banner cảnh báo phải hiển thị
        $response->assertSeeText(__('Phát hiện cảnh báo đối với giỏ hàng này:'));
        $response->assertSeeText('đã thay đổi từ');
    }

    public function test_admin_can_delete_cart(): void
    {
        $cart = Cart::create(['status' => CartStatus::ABANDONED]);

        $response = $this->actingAs($this->admin)->deleteJson(route('admin.carts.destroy', $cart->uuid));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    public function test_admin_delete_non_existent_cart_returns_404(): void
    {
        $nonExistentUuid = (string) \Illuminate\Support\Str::uuid();
        $response = $this->actingAs($this->admin)->deleteJson(route('admin.carts.destroy', $nonExistentUuid));

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Không tìm thấy giỏ hàng.',
        ]);
    }

    public function test_admin_can_bulk_delete_carts(): void
    {
        $cart1 = Cart::create(['status' => CartStatus::ABANDONED]);
        $cart2 = Cart::create(['status' => CartStatus::MERGED]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.carts.bulk'), [
            'action' => 'delete',
            'ids'    => [$cart1->uuid, $cart2->uuid],
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('carts', ['id' => $cart1->id]);
        $this->assertDatabaseMissing('carts', ['id' => $cart2->id]);
    }
}
