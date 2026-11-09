<?php

namespace Tests\Feature\Api;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartMergeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product1;
    private Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->product1 = Product::create([
            'sku'             => 'MERGE-01',
            'price'           => 100000,
            'is_active'       => true,
            'product_type'    => 'simple',
            'stock_quantity'  => 50,
            'track_inventory' => true,
        ]);
        $this->product1->translations()->create(['locale' => 'vi', 'name' => 'Sản phẩm 1', 'slug' => 'san-pham-1']);

        $this->product2 = Product::create([
            'sku'             => 'MERGE-02',
            'price'           => 200000,
            'is_active'       => true,
            'product_type'    => 'simple',
            'stock_quantity'  => 50,
            'track_inventory' => true,
        ]);
        $this->product2->translations()->create(['locale' => 'vi', 'name' => 'Sản phẩm 2', 'slug' => 'san-pham-2']);
    }

    public function test_merge_requires_authentication(): void
    {
        $guestCart = Cart::create(['status' => CartStatus::ACTIVE]);

        $response = $this->postJson(route('api.v1.cart.merge'), [
            'guest_cart_token' => $guestCart->uuid,
        ]);

        $response->assertStatus(401);
    }

    public function test_merge_guest_cart_when_user_has_no_active_cart(): void
    {
        $guestCart = Cart::create(['status' => CartStatus::ACTIVE]);
        CartItem::create([
            'cart_id'             => $guestCart->id,
            'product_id'          => $this->product1->id,
            'line_key'            => "p{$this->product1->id}-v0",
            'quantity'            => 2,
            'unit_price_snapshot' => 100000,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson(route('api.v1.cart.merge'), [
            'guest_cart_token' => $guestCart->uuid,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data'    => [
                'totals' => [
                    'item_count' => 2,
                    'subtotal'   => 200000,
                ],
            ],
        ]);

        $guestCart->refresh();
        $this->assertEquals(CartStatus::MERGED, $guestCart->status);

        $userCart = Cart::where('user_id', $this->user->id)->first();
        $this->assertNotNull($userCart);
        $this->assertEquals(CartStatus::ACTIVE, $userCart->status);
        $this->assertDatabaseHas('cart_items', [
            'cart_id'    => $userCart->id,
            'product_id' => $this->product1->id,
            'quantity'   => 2,
        ]);
    }

    public function test_merge_guest_cart_into_existing_user_cart_combines_quantities(): void
    {
        // 1. Tạo giỏ user đã có sẵn product1 (SL = 1) và product2 (SL = 1)
        $userCart = Cart::create([
            'user_id'  => $this->user->id,
            'status'   => CartStatus::ACTIVE,
            'currency' => 'VND',
        ]);
        CartItem::create([
            'cart_id'             => $userCart->id,
            'product_id'          => $this->product1->id,
            'line_key'            => "p{$this->product1->id}-v0",
            'quantity'            => 1,
            'unit_price_snapshot' => 100000,
        ]);

        // 2. Tạo giỏ guest có product1 (SL = 2) và product2 (SL = 3)
        $guestCart = Cart::create([
            'status'   => CartStatus::ACTIVE,
            'currency' => 'VND',
        ]);
        CartItem::create([
            'cart_id'             => $guestCart->id,
            'product_id'          => $this->product1->id,
            'line_key'            => "p{$this->product1->id}-v0",
            'quantity'            => 2,
            'unit_price_snapshot' => 100000,
        ]);
        CartItem::create([
            'cart_id'             => $guestCart->id,
            'product_id'          => $this->product2->id,
            'line_key'            => "p{$this->product2->id}-v0",
            'quantity'            => 3,
            'unit_price_snapshot' => 200000,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson(route('api.v1.cart.merge'), [
            'guest_cart_token' => $guestCart->uuid,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data'    => [
                'totals' => [
                    'item_count' => 6, // product1 (1+2=3) + product2 (3) = 6
                    'subtotal'   => 900000, // (3 * 100k) + (3 * 200k) = 900k
                ],
            ],
        ]);

        // Giỏ guest phải được chuyển sang trạng thái merged
        $guestCart->refresh();
        $this->assertEquals(CartStatus::MERGED, $guestCart->status);

        // Giỏ user có 2 dòng hàng với số lượng gộp
        $this->assertDatabaseHas('cart_items', [
            'cart_id'    => $userCart->id,
            'product_id' => $this->product1->id,
            'quantity'   => 3,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'cart_id'    => $userCart->id,
            'product_id' => $this->product2->id,
            'quantity'   => 3,
        ]);
    }

    public function test_merge_guest_cart_caps_quantity_for_sold_individually_product(): void
    {
        $this->product1->update([
            'sold_individually' => true,
        ]);

        $userCart = Cart::create([
            'user_id'  => $this->user->id,
            'status'   => CartStatus::ACTIVE,
            'currency' => 'VND',
        ]);
        CartItem::create([
            'cart_id'             => $userCart->id,
            'product_id'          => $this->product1->id,
            'line_key'            => "p{$this->product1->id}-v0",
            'quantity'            => 1,
            'unit_price_snapshot' => 100000,
        ]);

        $guestCart = Cart::create(['status' => CartStatus::ACTIVE]);
        CartItem::create([
            'cart_id'             => $guestCart->id,
            'product_id'          => $this->product1->id,
            'line_key'            => "p{$this->product1->id}-v0",
            'quantity'            => 1,
            'unit_price_snapshot' => 100000,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson(route('api.v1.cart.merge'), [
            'guest_cart_token' => $guestCart->uuid,
        ]);

        $response->assertStatus(200);

        // Do sold_individually = true, số lượng gộp tối đa chỉ là 1
        $this->assertDatabaseHas('cart_items', [
            'cart_id'    => $userCart->id,
            'product_id' => $this->product1->id,
            'quantity'   => 1,
        ]);

        $userItem = CartItem::where('cart_id', $userCart->id)->where('product_id', $this->product1->id)->first();
        $this->assertNotNull($userItem->meta['quantity_adjusted_notice'] ?? null);
        $this->assertStringContainsString('Số lượng được điều chỉnh về 1', $userItem->meta['quantity_adjusted_notice']);
    }
}
