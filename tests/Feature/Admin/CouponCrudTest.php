<?php

namespace Tests\Feature\Admin;

use App\Core\Enums\CouponType;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_guest_cannot_access_coupons_index(): void
    {
        $response = $this->get(route('admin.coupons.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_coupons_index(): void
    {
        Coupon::create([
            'code' => 'TESTCODE',
            'type' => CouponType::PERCENT,
            'value' => 15,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.coupons.index'));

        $response->assertStatus(200);
        $response->assertSee('TESTCODE');
    }

    public function test_admin_can_create_coupon(): void
    {
        $payload = [
            'code' => 'NEWYEAR2027',
            'name' => 'Mã tết 2027',
            'description' => 'Giảm 20% tối đa 100k',
            'type' => 'percent',
            'value' => 20,
            'min_order_value' => 200000,
            'max_discount_amount' => 100000,
            'usage_limit' => 50,
            'usage_per_user' => 1,
            'is_active' => '1',
            'cannot_combine_with_sale_items' => '1',
            'stackable' => '0',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.coupons.store'), $payload);

        $response->assertRedirect(route('admin.coupons.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('coupons', [
            'code' => 'NEWYEAR2027',
            'type' => 'percent',
            'value' => 20.00,
            'is_active' => 1,
            'cannot_combine_with_sale_items' => 1,
        ]);
    }

    public function test_coupon_validation_errors_redirect_back_with_session_errors(): void
    {
        Coupon::create([
            'code' => 'EXISTING',
            'type' => CouponType::FIXED,
            'value' => 50000,
        ]);

        $payload = [
            'code' => 'EXISTING', // Duplicated
            'type' => 'invalid_type', // Invalid enum
            'value' => -10, // Negative
        ];

        $response = $this->actingAs($this->admin)
            ->from(route('admin.coupons.create'))
            ->post(route('admin.coupons.store'), $payload);

        // Must redirect back with errors, NOT return JSON
        $response->assertRedirect(route('admin.coupons.create'));
        $response->assertSessionHasErrors(['code', 'type', 'value']);
    }

    public function test_admin_can_update_coupon(): void
    {
        $coupon = Coupon::create([
            'code' => 'OLDCODE',
            'name' => 'Old Name',
            'type' => CouponType::FIXED,
            'value' => 20000,
            'is_active' => true,
        ]);

        $payload = [
            'code' => 'UPDATEDCODE',
            'name' => 'Updated Name',
            'type' => 'fixed',
            'value' => 30000,
            'is_active' => '0',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.coupons.update', $coupon->uuid), $payload);

        $response->assertRedirect(route('admin.coupons.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'code' => 'UPDATEDCODE',
            'name' => 'Updated Name',
            'value' => 30000.00,
            'is_active' => 0,
        ]);
    }

    public function test_admin_can_toggle_coupon_status(): void
    {
        $coupon = Coupon::create([
            'code' => 'TOGGLEME',
            'type' => CouponType::PERCENT,
            'value' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.coupons.toggle-status', $coupon->uuid));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => ['is_active' => false],
        ]);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'is_active' => 0,
        ]);
    }

    public function test_admin_can_delete_coupon(): void
    {
        $coupon = Coupon::create([
            'code' => 'DELETEME',
            'type' => CouponType::PERCENT,
            'value' => 10,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.coupons.destroy', $coupon->uuid));

        $response->assertRedirect(route('admin.coupons.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('coupons', [
            'id' => $coupon->id,
        ]);
    }

    public function test_admin_can_bulk_actions_on_coupons(): void
    {
        $c1 = Coupon::create(['code' => 'BULK1', 'type' => CouponType::PERCENT, 'value' => 10, 'is_active' => false]);
        $c2 = Coupon::create(['code' => 'BULK2', 'type' => CouponType::PERCENT, 'value' => 10, 'is_active' => false]);

        // Bulk activate
        $response = $this->actingAs($this->admin)->post(route('admin.coupons.bulk'), [
            'bulk_module' => 'coupons',
            'action' => 'activate',
            'ids' => [$c1->id, $c2->id],
        ]);
        $response->assertRedirect();
        $this->assertTrue((bool)$c1->fresh()->is_active);
        $this->assertTrue((bool)$c2->fresh()->is_active);

        // Bulk deactivate
        $this->actingAs($this->admin)->post(route('admin.coupons.bulk'), [
            'bulk_module' => 'coupons',
            'action' => 'deactivate',
            'ids' => [$c1->id, $c2->id],
        ]);
        $this->assertFalse((bool)$c1->fresh()->is_active);
        $this->assertFalse((bool)$c2->fresh()->is_active);

        // Bulk delete
        $this->actingAs($this->admin)->post(route('admin.coupons.bulk'), [
            'bulk_module' => 'coupons',
            'action' => 'delete',
            'ids' => [$c1->id, $c2->id],
        ]);
        $this->assertSoftDeleted('coupons', ['id' => $c1->id]);
        $this->assertSoftDeleted('coupons', ['id' => $c2->id]);
    }
}
