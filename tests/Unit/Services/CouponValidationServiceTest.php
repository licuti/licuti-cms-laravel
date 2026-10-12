<?php

namespace Tests\Unit\Services;

use App\Core\Enums\CouponType;
use App\Models\Coupon;
use App\Models\User;
use App\Services\Admin\Coupon\CouponValidationService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private CouponValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CouponValidationService::class);
    }

    public function test_validates_percent_discount_with_max_amount(): void
    {
        $coupon = Coupon::create([
            'code' => 'PERCENT10',
            'type' => CouponType::PERCENT,
            'value' => 10,
            'min_order_value' => 100000,
            'max_discount_amount' => 30000,
            'is_active' => true,
        ]);

        $result = $this->service->validate('PERCENT10', 500000, guestEmail: 'test@example.com');

        $this->assertTrue($result['is_valid']);
        // 10% of 500,000 is 50,000, capped at max_discount_amount 30,000
        $this->assertEquals(30000, $result['discount_amount']);
    }

    public function test_validates_fixed_discount(): void
    {
        $coupon = Coupon::create([
            'code' => 'FIXED50K',
            'type' => CouponType::FIXED,
            'value' => 50000,
            'min_order_value' => 200000,
            'is_active' => true,
        ]);

        $result = $this->service->validate('FIXED50K', 300000, guestEmail: 'test@example.com');

        $this->assertTrue($result['is_valid']);
        $this->assertEquals(50000, $result['discount_amount']);
    }

    public function test_rejects_when_cart_total_below_minimum_order(): void
    {
        Coupon::create([
            'code' => 'MIN300K',
            'type' => CouponType::FIXED,
            'value' => 30000,
            'min_order_value' => 300000,
            'is_active' => true,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(__('Đơn hàng chưa đạt giá trị tối thiểu để áp dụng mã này.'));

        $this->service->validate('MIN300K', 150000, guestEmail: 'test@example.com');
    }

    public function test_rejects_expired_coupon(): void
    {
        Coupon::create([
            'code' => 'EXPIRED',
            'type' => CouponType::PERCENT,
            'value' => 10,
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subDays(1),
            'is_active' => true,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(__('Mã giảm giá đã hết hạn sử dụng.'));

        $this->service->validate('EXPIRED', 200000, guestEmail: 'test@example.com');
    }

    public function test_rejects_inactive_coupon(): void
    {
        Coupon::create([
            'code' => 'INACTIVE',
            'type' => CouponType::PERCENT,
            'value' => 10,
            'is_active' => false,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(__('Mã giảm giá không tồn tại hoặc đã bị vô hiệu hóa.'));

        $this->service->validate('INACTIVE', 200000, guestEmail: 'test@example.com');
    }

    public function test_rejects_when_global_usage_limit_reached(): void
    {
        Coupon::create([
            'code' => 'LIMITED',
            'type' => CouponType::FIXED,
            'value' => 20000,
            'usage_limit' => 5,
            'used_count' => 5,
            'is_active' => true,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(__('Mã giảm giá đã hết lượt sử dụng.'));

        $this->service->validate('LIMITED', 200000, guestEmail: 'test@example.com');
    }

    public function test_rejects_when_per_user_limit_reached(): void
    {
        $user = User::factory()->create();

        $coupon = Coupon::create([
            'code' => 'ONCEPERUSER',
            'type' => CouponType::FIXED,
            'value' => 20000,
            'usage_per_user' => 1,
            'is_active' => true,
        ]);

        // Record 1 usage
        $coupon->usages()->create([
            'user_id' => $user->id,
            'discount_amount' => 20000,
            'used_at' => now(),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(__('Bạn đã hết lượt sử dụng mã giảm giá này.'));

        $this->service->validate('ONCEPERUSER', 200000, userId: $user->id);
    }

    public function test_apply_and_deduct_increments_used_count_and_creates_record(): void
    {
        $user = User::factory()->create();

        $coupon = Coupon::create([
            'code' => 'APPLYTEST',
            'type' => CouponType::FIXED,
            'value' => 25000,
            'usage_limit' => 10,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $this->service->applyAndDeduct($coupon, 25000, userId: $user->id, orderId: 999);

        $coupon->refresh();
        $this->assertEquals(1, $coupon->used_count);

        $this->assertDatabaseHas('coupon_users', [
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'order_id' => 999,
            'discount_amount' => 25000,
        ]);
    }
}
