<?php

namespace Tests\Feature\Admin;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_guest_cannot_access_payment_methods_index(): void
    {
        $response = $this->get(route('admin.payment-methods.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_payment_methods_index(): void
    {
        PaymentMethod::create([
            'code' => 'vnpay',
            'name' => 'Cổng VNPAY',
            'description' => 'Thanh toán quét mã QR',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.payment-methods.index'));

        $response->assertStatus(200);
        $response->assertSee('vnpay');
        $response->assertSee('Cổng VNPAY');
    }

    public function test_admin_can_create_payment_method(): void
    {
        $payload = [
            'code' => 'momo',
            'name' => 'Ví điện tử MoMo',
            'description' => 'Thanh toán qua ví MoMo',
            'is_active' => '1',
            'submit_action' => 'save',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.payment-methods.store'), $payload);

        $response->assertRedirect(route('admin.payment-methods.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payment_methods', [
            'code' => 'momo',
            'name' => 'Ví điện tử MoMo',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_payment_method_with_save_and_edit(): void
    {
        $payload = [
            'code' => 'zalopay',
            'name' => 'Ví ZaloPay',
            'description' => 'Thanh toán qua ZaloPay',
            'is_active' => '1',
            'submit_action' => 'save_and_edit',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.payment-methods.store'), $payload);

        $created = PaymentMethod::where('code', 'zalopay')->firstOrFail();
        $response->assertRedirect(route('admin.payment-methods.edit', $created->uuid));
        $response->assertSessionHas('success');
    }

    public function test_admin_can_update_payment_method(): void
    {
        $pm = PaymentMethod::create([
            'code' => 'stripe',
            'name' => 'Thẻ quốc tế Stripe',
            'is_active' => true,
        ]);

        $payload = [
            'code' => 'stripe',
            'name' => 'Cổng Stripe Quốc Tế',
            'is_active' => '0',
            'submit_action' => 'save',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.payment-methods.update', $pm->uuid), $payload);

        $response->assertRedirect(route('admin.payment-methods.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payment_methods', [
            'id' => $pm->id,
            'name' => 'Cổng Stripe Quốc Tế',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_toggle_payment_method_status(): void
    {
        $pm = PaymentMethod::create([
            'code' => 'cod',
            'name' => 'Thanh toán khi nhận hàng',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.payment-methods.toggle-status', $pm->uuid));

        $response->assertRedirect();
        $this->assertFalse((bool)$pm->fresh()->is_active);
    }

    public function test_admin_can_delete_payment_method(): void
    {
        $pm = PaymentMethod::create([
            'code' => 'old_gateway',
            'name' => 'Cổng cũ',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson(route('admin.payment-methods.destroy', $pm->uuid));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('payment_methods', ['id' => $pm->id]);
    }

    public function test_admin_can_bulk_actions_on_payment_methods(): void
    {
        $p1 = PaymentMethod::create(['code' => 'BULK_P1', 'name' => 'Method 1', 'is_active' => false]);
        $p2 = PaymentMethod::create(['code' => 'BULK_P2', 'name' => 'Method 2', 'is_active' => false]);

        // Bulk activate
        $response = $this->actingAs($this->admin)->post(route('admin.payment-methods.bulk'), [
            'bulk_module' => 'payment_methods',
            'action' => 'activate',
            'ids' => [$p1->id, $p2->id],
        ]);
        $response->assertRedirect();
        $this->assertTrue((bool)$p1->fresh()->is_active);
        $this->assertTrue((bool)$p2->fresh()->is_active);

        // Bulk deactivate
        $this->actingAs($this->admin)->post(route('admin.payment-methods.bulk'), [
            'bulk_module' => 'payment_methods',
            'action' => 'deactivate',
            'ids' => [$p1->id, $p2->id],
        ]);
        $this->assertFalse((bool)$p1->fresh()->is_active);
        $this->assertFalse((bool)$p2->fresh()->is_active);

        // Bulk delete
        $this->actingAs($this->admin)->post(route('admin.payment-methods.bulk'), [
            'bulk_module' => 'payment_methods',
            'action' => 'delete',
            'ids' => [$p1->id, $p2->id],
        ]);
        $this->assertDatabaseMissing('payment_methods', ['id' => $p1->id]);
        $this->assertDatabaseMissing('payment_methods', ['id' => $p2->id]);
    }
}
