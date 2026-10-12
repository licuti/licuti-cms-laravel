<?php

namespace Tests\Feature\Admin;

use App\Core\Enums\ReviewStatus;
use App\Events\ReviewApproved;
use App\Events\ReviewRejected;
use App\Models\Language;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProductReviewCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);

        Language::create([
            'code' => 'vi',
            'name' => 'Tiếng Việt',
            'native_name' => 'Tiếng Việt',
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'sku' => 'PROD-TEST-1',
            'price' => 100000,
        ]);
        $this->product->translations()->create([
            'locale' => 'vi',
            'name' => 'Tai nghe không dây Pro',
            'slug' => 'tai-nghe-khong-day-pro',
        ]);
    }

    public function test_guest_cannot_access_product_reviews_index(): void
    {
        $response = $this->get(route('admin.product-reviews.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_product_reviews_index_with_translated_product_name(): void
    {
        ProductReview::create([
            'product_id' => $this->product->id,
            'user_id' => $this->admin->id,
            'rating' => 5,
            'content' => 'Sản phẩm nghe nhạc cực hay!',
            'status' => ReviewStatus::APPROVED,
            'is_verified_purchase' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.product-reviews.index'));

        $response->assertStatus(200);
        $response->assertSee('Tai nghe không dây Pro');
        $response->assertSee('Sản phẩm nghe nhạc cực hay!');
        $response->assertSee('Đã duyệt');
    }

    public function test_admin_can_filter_reviews_by_status(): void
    {
        ProductReview::create([
            'product_id' => $this->product->id,
            'rating' => 5,
            'content' => 'Review được duyệt',
            'status' => ReviewStatus::APPROVED,
        ]);

        ProductReview::create([
            'product_id' => $this->product->id,
            'rating' => 2,
            'content' => 'Review đang chờ',
            'status' => ReviewStatus::PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.product-reviews.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('Review đang chờ');
        $response->assertDontSee('Review được duyệt');
    }

    public function test_admin_can_approve_review(): void
    {
        Event::fake([ReviewApproved::class]);

        $review = ProductReview::create([
            'product_id' => $this->product->id,
            'rating' => 5,
            'content' => 'Chờ duyệt',
            'status' => ReviewStatus::PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-reviews.approve', $review->uuid));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'status' => 'approved',
        ]);

        Event::assertDispatched(ReviewApproved::class);
    }

    public function test_admin_can_reject_review(): void
    {
        Event::fake([ReviewRejected::class]);

        $review = ProductReview::create([
            'product_id' => $this->product->id,
            'rating' => 1,
            'content' => 'Sp xau te',
            'status' => ReviewStatus::PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.product-reviews.reject', $review->uuid));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'status' => 'rejected',
        ]);

        Event::assertDispatched(ReviewRejected::class);
    }

    public function test_admin_can_delete_review(): void
    {
        $review = ProductReview::create([
            'product_id' => $this->product->id,
            'rating' => 3,
            'content' => 'Xoa toi di',
            'status' => ReviewStatus::PENDING,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.product-reviews.destroy', $review->uuid));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('product_reviews', [
            'id' => $review->id,
        ]);
    }
}
