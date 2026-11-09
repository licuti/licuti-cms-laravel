<?php

namespace Tests\Feature\Api;

use App\Core\Enums\CouponType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::create([
            'sku'             => 'PROD-001',
            'price'           => 150000,
            'is_active'       => true,
            'product_type'    => 'simple',
            'stock_quantity'  => 50,
            'track_inventory' => true,
        ]);

        $this->product->translations()->create([
            'locale' => 'vi',
            'name'   => 'Áo Polo Thể Thao Nam',
            'slug'   => 'ao-polo-the-thao-nam',
        ]);
    }

    public function test_get_cart_generates_new_token_when_none_provided(): void
    {
        $response = $this->getJson(route('api.v1.cart.index'));

        $response->assertStatus(200);
        $response->assertHeader('X-Cart-Token');
        $token = $response->headers->get('X-Cart-Token');

        $response->assertJson([
            'success' => true,
            'data'    => [
                'cart_token' => $token,
                'items'      => [],
                'totals'     => [
                    'item_count' => 0,
                    'subtotal'   => 0,
                ],
            ],
        ]);

        $this->assertDatabaseHas('carts', ['uuid' => $token]);
    }

    public function test_get_cart_reuses_token_from_header(): void
    {
        $cart = Cart::create(['currency' => 'VND']);

        $response = $this->withHeader('X-Cart-Token', $cart->uuid)
            ->getJson(route('api.v1.cart.index'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data'    => [
                'cart_token' => $cart->uuid,
            ],
        ]);
    }

    public function test_add_item_to_cart_success(): void
    {
        $response = $this->postJson(route('api.v1.cart.items.add'), [
            'product_uuid' => $this->product->uuid,
            'quantity'     => 2,
        ]);

        $response->assertStatus(201);
        $token = $response->headers->get('X-Cart-Token');
        $this->assertNotEmpty($token);

        $response->assertJson([
            'success' => true,
            'data'    => [
                'totals' => [
                    'item_count' => 2,
                    'subtotal'   => 300000,
                ],
            ],
        ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ]);
    }

    public function test_add_same_item_increments_quantity(): void
    {
        // Lần 1: thêm 2
        $res1 = $this->postJson(route('api.v1.cart.items.add'), [
            'product_uuid' => $this->product->uuid,
            'quantity'     => 2,
        ]);
        $token = $res1->headers->get('X-Cart-Token');

        // Lần 2: thêm tiếp 3 cùng token
        $res2 = $this->withHeader('X-Cart-Token', $token)
            ->postJson(route('api.v1.cart.items.add'), [
                'product_uuid' => $this->product->uuid,
                'quantity'     => 3,
            ]);

        $res2->assertStatus(201);
        $res2->assertJson([
            'success' => true,
            'data'    => [
                'totals' => [
                    'item_count' => 5,
                    'subtotal'   => 750000,
                ],
            ],
        ]);
    }

    public function test_add_item_rejects_insufficient_stock(): void
    {
        $response = $this->postJson(route('api.v1.cart.items.add'), [
            'product_uuid' => $this->product->uuid,
            'quantity'     => 999, // Vượt quá stock 50
        ]);

        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_update_item_quantity(): void
    {
        $addRes = $this->postJson(route('api.v1.cart.items.add'), [
            'product_uuid' => $this->product->uuid,
            'quantity'     => 1,
        ]);
        $token = $addRes->headers->get('X-Cart-Token');
        $itemUuid = $addRes->json('data.items.0.uuid');

        $updateRes = $this->withHeader('X-Cart-Token', $token)
            ->patchJson(route('api.v1.cart.items.update', $itemUuid), [
                'quantity' => 4,
            ]);

        $updateRes->assertStatus(200);
        $updateRes->assertJson([
            'success' => true,
            'data'    => [
                'totals' => [
                    'item_count' => 4,
                    'subtotal'   => 600000,
                ],
            ],
        ]);
    }

    public function test_remove_item_from_cart(): void
    {
        $addRes = $this->postJson(route('api.v1.cart.items.add'), [
            'product_uuid' => $this->product->uuid,
            'quantity'     => 2,
        ]);
        $token = $addRes->headers->get('X-Cart-Token');
        $itemUuid = $addRes->json('data.items.0.uuid');

        $deleteRes = $this->withHeader('X-Cart-Token', $token)
            ->deleteJson(route('api.v1.cart.items.remove', $itemUuid));

        $deleteRes->assertStatus(200);
        $deleteRes->assertJson([
            'success' => true,
            'data'    => [
                'totals' => [
                    'item_count' => 0,
                    'subtotal'   => 0,
                ],
            ],
        ]);

        $this->assertDatabaseMissing('cart_items', ['uuid' => $itemUuid]);
    }

    public function test_clear_cart(): void
    {
        $addRes = $this->postJson(route('api.v1.cart.items.add'), [
            'product_uuid' => $this->product->uuid,
            'quantity'     => 2,
        ]);
        $token = $addRes->headers->get('X-Cart-Token');

        $clearRes = $this->withHeader('X-Cart-Token', $token)
            ->deleteJson(route('api.v1.cart.clear'));

        $clearRes->assertStatus(200);
        $clearRes->assertJson([
            'success' => true,
            'data'    => [
                'totals' => [
                    'item_count' => 0,
                ],
            ],
        ]);
    }

    public function test_apply_and_remove_coupon(): void
    {
        $coupon = Coupon::create([
            'code'            => 'TEST50K',
            'type'            => CouponType::FIXED,
            'value'           => 50000,
            'min_order_value' => 100000,
            'is_active'       => true,
        ]);

        $addRes = $this->postJson(route('api.v1.cart.items.add'), [
            'product_uuid' => $this->product->uuid,
            'quantity'     => 2, // 300.000 > min 100.000
        ]);
        $token = $addRes->headers->get('X-Cart-Token');

        // Apply
        $couponRes = $this->withHeader('X-Cart-Token', $token)
            ->postJson(route('api.v1.cart.coupon.apply'), [
                'code'        => 'TEST50K',
                'guest_email' => 'customer@example.com',
            ]);

        $couponRes->assertStatus(200);
        $couponRes->assertJson([
            'success' => true,
            'data'    => [
                'coupon' => [
                    'code'            => 'TEST50K',
                    'discount_amount' => 50000,
                ],
                'totals' => [
                    'grand_total'     => 250000,
                ],
            ],
        ]);

        // Remove
        $removeRes = $this->withHeader('X-Cart-Token', $token)
            ->deleteJson(route('api.v1.cart.coupon.remove'));

        $removeRes->assertStatus(200);
        $removeRes->assertJson([
            'success' => true,
            'data'    => [
                'coupon' => null,
                'totals' => [
                    'grand_total' => 300000,
                ],
            ],
        ]);
    }

    public function test_idor_protection_cannot_modify_other_cart_item(): void
    {
        $cart1Res = $this->postJson(route('api.v1.cart.items.add'), [
            'product_uuid' => $this->product->uuid,
            'quantity'     => 1,
        ]);
        $item1Uuid = $cart1Res->json('data.items.0.uuid');

        // Giỏ 2 độc lập
        $cart2Res = $this->postJson(route('api.v1.cart.items.add'), [
            'product_uuid' => $this->product->uuid,
            'quantity'     => 1,
        ]);
        $token2 = $cart2Res->headers->get('X-Cart-Token');

        // Giỏ 2 cố tình xóa item của giỏ 1
        $attackRes = $this->withHeader('X-Cart-Token', $token2)
            ->deleteJson(route('api.v1.cart.items.remove', $item1Uuid));

        $attackRes->assertStatus(404);
    }
}
