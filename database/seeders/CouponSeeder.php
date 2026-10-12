<?php

namespace Database\Seeders;

use App\Core\Enums\CouponType;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'Giảm 10% cho khách hàng mới',
                'description' => 'Áp dụng cho đơn hàng đầu tiên từ 100.000đ, giảm tối đa 50.000đ.',
                'type' => CouponType::PERCENT,
                'value' => 10,
                'min_order_value' => 100000,
                'max_discount_amount' => 50000,
                'usage_limit' => 500,
                'usage_per_user' => 1,
                'used_count' => 5,
                'start_date' => now()->subDays(10),
                'end_date' => now()->addMonths(3),
                'is_active' => true,
                'cannot_combine_with_sale_items' => true,
                'stackable' => false,
            ],
            [
                'code' => 'FREESHIP',
                'name' => 'Miễn phí vận chuyển toàn quốc',
                'description' => 'Áp dụng cho đơn hàng từ 250.000đ.',
                'type' => CouponType::FREE_SHIPPING,
                'value' => 0,
                'min_order_value' => 250000,
                'max_discount_amount' => 30000,
                'usage_limit' => 1000,
                'usage_per_user' => 3,
                'used_count' => 12,
                'start_date' => now()->subDays(5),
                'end_date' => now()->addMonths(6),
                'is_active' => true,
                'cannot_combine_with_sale_items' => false,
                'stackable' => true,
            ],
            [
                'code' => 'GIAM50K',
                'name' => 'Giảm trực tiếp 50.000đ',
                'description' => 'Giảm ngay 50.000đ cho đơn hàng từ 300.000đ.',
                'type' => CouponType::FIXED,
                'value' => 50000,
                'min_order_value' => 300000,
                'max_discount_amount' => 50000,
                'usage_limit' => 100,
                'usage_per_user' => 1,
                'used_count' => 2,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(20),
                'is_active' => true,
                'cannot_combine_with_sale_items' => true,
                'stackable' => false,
            ],
            [
                'code' => 'VIP20',
                'name' => 'Ưu đãi VIP 20%',
                'description' => 'Dành cho khách hàng VIP, giảm tối đa 200.000đ cho đơn từ 500.000đ.',
                'type' => CouponType::PERCENT,
                'value' => 20,
                'min_order_value' => 500000,
                'max_discount_amount' => 200000,
                'usage_limit' => 50,
                'usage_per_user' => 2,
                'used_count' => 0,
                'start_date' => now()->subDay(),
                'end_date' => now()->addMonths(1),
                'is_active' => true,
                'cannot_combine_with_sale_items' => false,
                'stackable' => true,
            ],
            [
                'code' => 'EXPIRED2025',
                'name' => 'Mã giảm giá đã hết hạn',
                'description' => 'Mã khuyến mãi chương trình cuối năm ngoái.',
                'type' => CouponType::FIXED,
                'value' => 30000,
                'min_order_value' => 150000,
                'max_discount_amount' => 30000,
                'usage_limit' => 200,
                'usage_per_user' => 1,
                'used_count' => 195,
                'start_date' => now()->subMonths(6),
                'end_date' => now()->subMonths(1),
                'is_active' => true,
                'cannot_combine_with_sale_items' => false,
                'stackable' => false,
            ],
            [
                'code' => 'TATHOATDONG',
                'name' => 'Mã tạm ngưng áp dụng',
                'description' => 'Tạm ngừng chương trình khuyến mãi.',
                'type' => CouponType::PERCENT,
                'value' => 15,
                'min_order_value' => 200000,
                'max_discount_amount' => 60000,
                'usage_limit' => 50,
                'usage_per_user' => 1,
                'used_count' => 0,
                'start_date' => now()->subDays(1),
                'end_date' => now()->addDays(30),
                'is_active' => false,
                'cannot_combine_with_sale_items' => true,
                'stackable' => false,
            ],
        ];

        $firstUser = User::first();

        foreach ($coupons as $data) {
            $coupon = Coupon::firstOrCreate(
                ['code' => $data['code']],
                array_merge($data, ['uuid' => (string) Str::uuid()])
            );

            // Add sample usage for WELCOME10 if user exists
            if ($coupon->code === 'WELCOME10' && $firstUser && $coupon->usages()->count() === 0) {
                $coupon->usages()->create([
                    'user_id' => $firstUser->id,
                    'discount_amount' => 35000,
                    'used_at' => now()->subDays(2),
                ]);
            }
        }
    }
}
