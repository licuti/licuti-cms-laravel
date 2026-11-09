<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class, // 1. Tạo roles & permissions trước
            AdminUserSeeder::class,      // 2. Tạo admin user và gán role
            PaymentMethodSeeder::class,  // 3. Cổng thanh toán (COD + VietQR bật sẵn) — mở khóa checkout thật
            CouponSeeder::class,         // 4. Dữ liệu mẫu Coupons
            ProductReviewSeeder::class,  // 5. Dữ liệu mẫu Product Reviews
        ]);
    }
}
