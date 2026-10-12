<?php

namespace Database\Seeders;

use App\Core\Enums\ReviewStatus;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $users = User::all();

        if ($products->isEmpty()) {
            return;
        }

        $sampleReviews = [
            [
                'rating' => 5,
                'content' => 'Sản phẩm dùng rất tốt, đóng gói cẩn thận, giao hàng nhanh chóng!',
                'status' => ReviewStatus::APPROVED,
                'is_verified_purchase' => true,
                'helpful_count' => 12,
            ],
            [
                'rating' => 4,
                'content' => 'Chất lượng ổn trong tầm giá, nhân viên hỗ trợ nhiệt tình, sẽ ủng hộ tiếp.',
                'status' => ReviewStatus::APPROVED,
                'is_verified_purchase' => true,
                'helpful_count' => 5,
            ],
            [
                'rating' => 5,
                'content' => 'Hàng chính hãng, tem mác niêm phong đầy đủ, rất ưng ý!',
                'status' => ReviewStatus::PENDING,
                'is_verified_purchase' => false,
                'helpful_count' => 0,
            ],
            [
                'rating' => 3,
                'content' => 'Giao hàng hơi chậm một chút nhưng sản phẩm dùng tạm ổn.',
                'status' => ReviewStatus::PENDING,
                'is_verified_purchase' => true,
                'helpful_count' => 1,
            ],
            [
                'rating' => 1,
                'content' => 'Shop lừa đảo, sản phẩm không giống hình chụp, đừng ai mua!',
                'status' => ReviewStatus::REJECTED,
                'is_verified_purchase' => false,
                'helpful_count' => 0,
            ],
            [
                'rating' => 5,
                'content' => 'Đã mua lần thứ hai tại Licuti, chất lượng dịch vụ luôn tuyệt vời.',
                'status' => ReviewStatus::APPROVED,
                'is_verified_purchase' => true,
                'helpful_count' => 8,
            ],
        ];

        foreach ($products as $productIndex => $product) {
            foreach ($sampleReviews as $reviewIndex => $data) {
                $user = $users->isNotEmpty() ? $users[($productIndex + $reviewIndex) % $users->count()] : null;

                ProductReview::create([
                    'uuid' => (string) Str::uuid(),
                    'product_id' => $product->id,
                    'user_id' => $user?->id,
                    'rating' => $data['rating'],
                    'content' => $data['content'],
                    'status' => $data['status'],
                    'is_verified_purchase' => $data['is_verified_purchase'],
                    'helpful_count' => $data['helpful_count'],
                ]);
            }
        }
    }
}
