<?php

namespace Tests\Feature\Commands;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_abandoned_command_updates_inactive_carts(): void
    {
        // Giỏ cũ: không hoạt động từ 30 giờ trước
        $oldCart = Cart::create([
            'status'           => CartStatus::ACTIVE,
            'last_activity_at' => now()->subHours(30),
        ]);

        // Giỏ mới: vừa hoạt động 2 giờ trước
        $recentCart = Cart::create([
            'status'           => CartStatus::ACTIVE,
            'last_activity_at' => now()->subHours(2),
        ]);

        $this->artisan('cart:mark-abandoned', ['--hours' => 24])
            ->assertExitCode(0);

        $oldCart->refresh();
        $recentCart->refresh();

        $this->assertEquals(CartStatus::ABANDONED, $oldCart->status);
        $this->assertEquals(CartStatus::ACTIVE, $recentCart->status);
    }

    public function test_purge_command_removes_stale_carts_and_items(): void
    {
        $product = Product::create([
            'sku'             => 'PURGE-01',
            'price'           => 50000,
            'is_active'       => true,
            'product_type'    => 'simple',
            'stock_quantity'  => 10,
            'track_inventory' => true,
        ]);

        // Giỏ guest cũ (abandoned từ 40 ngày trước)
        $staleCart = Cart::create([
            'user_id'    => null,
            'status'     => CartStatus::ABANDONED,
            'updated_at' => now()->subDays(40),
            'created_at' => now()->subDays(40),
        ]);
        CartItem::create([
            'cart_id'             => $staleCart->id,
            'product_id'          => $product->id,
            'line_key'            => "p{$product->id}-v0",
            'quantity'            => 1,
            'unit_price_snapshot' => 50000,
        ]);

        // Đảm bảo updated_at lùi 40 ngày sau khi tạo items
        Cart::where('id', $staleCart->id)->update([
            'updated_at' => now()->subDays(40),
            'created_at' => now()->subDays(40),
        ]);

        // Giỏ guest mới (abandoned từ 5 ngày trước)
        $recentCart = Cart::create([
            'user_id'    => null,
            'status'     => CartStatus::ABANDONED,
            'updated_at' => now()->subDays(5),
            'created_at' => now()->subDays(5),
        ]);

        $this->artisan('cart:purge', ['--guest-days' => 30, '--user-days' => 90])
            ->assertExitCode(0);

        $this->assertDatabaseMissing('carts', ['id' => $staleCart->id]);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $staleCart->id]);
        $this->assertDatabaseHas('carts', ['id' => $recentCart->id]);
    }
}
