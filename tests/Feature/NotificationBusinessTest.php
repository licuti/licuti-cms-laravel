<?php

namespace Tests\Feature;

use App\Core\Enums\ReviewStatus;
use App\Core\Payment\DTOs\PaymentResult;
use App\DTOs\Notification\NotificationEventData;
use App\DTOs\Order\OrderDTO;
use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Events\Order\OrderCancelled;
use App\Events\Order\OrderPlaced;
use App\Events\Payment\PaymentFailed;
use App\Events\Payment\PaymentSucceeded;
use App\Events\Product\LowStockThresholdReached;
use App\Events\ReviewApproved;
use App\Listeners\Notification\RecordOrderCancelledNotification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Admin\Order\OrderService;
use App\Services\Admin\ProductReview\ProductReviewService;
use App\Services\Shared\Notification\NotificationService;
use App\Services\Shared\Product\StockService;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NotificationBusinessTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('licuti_cms_test', DB::connection()->getDatabaseName());
    }

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Notification::fake();
        app()->setLocale('vi');
    }

    public function test_discovery_records_order_only_after_shipping_items_and_history_exist(): void
    {
        $this->mock(NotificationService::class)->shouldReceive('record')->once()
            ->withArgs(function (NotificationEventData $event): bool {
                $order = Order::where('uuid', $event->adminPayload['target_uuid'])->firstOrFail();
                $this->assertSame(1, $order->items()->count());
                $this->assertSame(1, $order->statusHistories()->count());
                $this->assertSame('guest@example.test', $order->shippingAddress->email);
                $this->assertGreaterThan(1, DB::transactionLevel());
                $this->assertSame(NotificationType::ORDER_PLACED, $event->type);
                $this->assertSame('guest@example.test', $event->mailRecipient);
                $this->assertSame('order-customer:'.$order->uuid, $event->mailRecipientKey);
                $this->assertNull($event->mailUserId);
                $this->assertStringContainsString('250.000 VND', $event->mailPayload['lines'][0]);
                $this->assertStringContainsString($order->order_number, $event->mailPayload['lines'][0]);
                $this->assertStringContainsString('không phải xác nhận thanh toán', $event->mailPayload['lines'][0]);

                return true;
            });

        $this->createOrder();
    }

    public function test_order_replay_keeps_one_delivery_per_channel_and_stable_customer_key(): void
    {
        $this->staff('orders.view');
        $order = $this->createOrder();
        $eventKey = NotificationType::ORDER_PLACED->value.':'.$order->uuid;
        $originalPayload = $this->deliveries($eventKey)->where('channel', 'mail')->value('payload');
        $order->shippingAddress->update(['email' => 'changed@example.test']);

        OrderPlaced::dispatch($order->fresh());
        OrderPlaced::dispatch($order->fresh());

        $this->assertSame(2, $this->deliveries($eventKey)->count());
        $this->assertSame(1, $this->deliveries($eventKey)->where('channel', 'mail')->count());
        $this->assertSame($originalPayload, $this->deliveries($eventKey)->where('channel', 'mail')->value('payload'));
        $this->assertDatabaseHas('notification_deliveries', [
            'event_key' => $eventKey, 'recipient_key' => 'order-customer:'.$order->uuid, 'channel' => 'mail',
        ]);
    }

    public function test_shipping_email_precedes_linked_user_and_invalid_shipping_falls_back_only_to_linked_user(): void
    {
        $snapshots = [];
        $this->captureSnapshots($snapshots);
        $linkedUser = User::factory()->create(['email' => 'linked@example.test']);
        User::factory()->create(['email' => 'unrelated@example.test']);

        $this->createOrder($linkedUser, 'shipping@example.test');
        $this->createOrder($linkedUser, 'invalid-email');
        $this->createOrder(null, 'invalid-email');
        $this->createOrder(null, 'unrelated@example.test');

        $this->assertSame('shipping@example.test', $snapshots[0]->mailRecipient);
        $this->assertSame('linked@example.test', $snapshots[1]->mailRecipient);
        $this->assertSame($linkedUser->id, $snapshots[1]->mailUserId);
        $this->assertNull($snapshots[2]->mailRecipient);
        $this->assertNotNull($snapshots[2]->mailRecipientKey);
        $this->assertSame('unrelated@example.test', $snapshots[3]->mailRecipient);
        $this->assertNull($snapshots[3]->mailUserId);
    }

    public function test_outer_order_rollback_removes_order_and_delivery_intents_without_sending(): void
    {
        $this->staff('orders.view');
        $orderUuid = null;

        try {
            DB::transaction(function () use (&$orderUuid) {
                $order = $this->createOrder();
                $orderUuid = $order->uuid;
                $this->assertSame(2, $this->deliveries(NotificationType::ORDER_PLACED->value.':'.$orderUuid)->count());
                throw new RuntimeException('Enclosing checkout failed');
            });
            $this->fail('Expected checkout rollback.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Enclosing checkout failed', $exception->getMessage());
        }

        $this->assertNotNull($orderUuid);
        $this->assertDatabaseMissing('orders', ['uuid' => $orderUuid]);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('shipping_addresses', 0);
        $this->assertDatabaseCount('order_status_histories', 0);
        $this->assertDatabaseCount('notification_deliveries', 0);
        $this->assertDatabaseCount('notifications', 0);
        Queue::assertNothingPushed();
        Notification::assertNothingSent();
    }

    public function test_cancellation_notification_replay_is_safe_and_does_not_copy_internal_reason(): void
    {
        $snapshots = [];
        $this->captureSnapshots($snapshots);
        $order = $this->createOrder();
        $reason = 'INTERNAL gateway secret https://internal.example/admin/orders';
        app(OrderService::class)->cancel($order->uuid, $reason);

        $cancelled = $snapshots[1];
        $this->assertSame(NotificationType::ORDER_CANCELLED, $cancelled->type);
        $this->assertSame(NotificationType::ORDER_CANCELLED->value.':'.$order->uuid, $cancelled->eventKey);
        $this->assertStringContainsString($order->order_number, $cancelled->mailPayload['lines'][0]);
        $this->assertStringContainsString('250.000 VND', $cancelled->mailPayload['lines'][0]);
        $this->assertStringNotContainsString('INTERNAL', json_encode($cancelled->mailPayload));
        $this->assertStringNotContainsString('/admin', json_encode($cancelled->mailPayload));
    }

    public function test_cancellation_listener_replay_deduplicates_without_replaying_stock_release(): void
    {
        $this->staff('orders.view');
        $order = $this->createOrder();
        app(OrderService::class)->cancel($order->uuid, 'Private reason');
        $event = new OrderCancelled($order->fresh(), 'Another private reason');
        // Replay only the notification handler: the existing release listener is intentionally unchanged.
        app(RecordOrderCancelledNotification::class)->handle($event);
        app(RecordOrderCancelledNotification::class)->handle($event);

        $this->assertSame(2, $this->deliveries(NotificationType::ORDER_CANCELLED->value.':'.$order->uuid)->count());
    }

    public function test_cancellation_stock_release_stays_synchronous_and_rolls_back_with_notifications(): void
    {
        $product = $this->stockProduct(['stock_quantity' => 7]);
        $order = $this->createOrder();
        $order->items()->first()->update(['product_id' => $product->id, 'quantity' => 3]);

        try {
            DB::transaction(function () use ($order, $product) {
                app(OrderService::class)->cancel($order->uuid, 'Private reason');
                $this->assertSame(10, (int) $product->fresh()->stock_quantity);
                $this->assertSame(1, $this->deliveries(NotificationType::ORDER_CANCELLED->value.':'.$order->uuid)->count());
                throw new RuntimeException('Cancel rollback');
            });
            $this->fail('Expected cancellation rollback.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Cancel rollback', $exception->getMessage());
        }

        $this->assertSame(7, (int) $product->fresh()->stock_quantity);
        $this->assertSame(OrderStatus::PENDING, $order->fresh()->status);
        $this->assertSame(0, $this->deliveries(NotificationType::ORDER_CANCELLED->value.':'.$order->uuid)->count());
    }

    public function test_review_repeated_approval_and_reapproval_send_only_one_author_delivery(): void
    {
        $this->staff('products.view');
        $review = $this->review(User::factory()->create());
        $service = app(ProductReviewService::class);
        $approvedEvents = 0;
        Event::listen(ReviewApproved::class, function () use (&$approvedEvents) {
            $approvedEvents++;
        });

        $service->approve($review->uuid);
        $service->approve($review->uuid);
        $this->assertSame(1, $approvedEvents);
        $service->reject($review->uuid);
        $service->approve($review->uuid);
        ReviewApproved::dispatch($review->fresh());

        $eventKey = NotificationType::REVIEW_APPROVED->value.':'.$review->uuid;
        $this->assertSame(3, $approvedEvents);
        $this->assertSame(1, $this->deliveries($eventKey)->count());
        $this->assertDatabaseHas('notification_deliveries', [
            'event_key' => $eventKey, 'channel' => 'mail',
            'recipient_key' => 'review-author:'.$review->uuid, 'recipient_user_id' => $review->user_id,
        ]);
    }

    public function test_missing_deleted_or_invalid_review_author_is_skipped_without_admin_delivery(): void
    {
        $this->staff('products.view');
        $deletedAuthor = User::factory()->create();
        $deletedReview = $this->review($deletedAuthor);
        $deletedAuthor->delete();
        $invalidAuthor = User::factory()->create(['email' => 'invalid-email']);

        foreach ([$this->review(), $deletedReview, $this->review($invalidAuthor)] as $review) {
            app(ProductReviewService::class)->approve($review->uuid);
            $eventKey = NotificationType::REVIEW_APPROVED->value.':'.$review->uuid;
            $this->assertSame(1, $this->deliveries($eventKey)->count());
            $this->assertDatabaseHas('notification_deliveries', [
                'event_key' => $eventKey, 'channel' => 'mail', 'status' => 'skipped',
                'recipient_key' => 'review-author:'.$review->uuid,
            ]);
        }
    }

    public function test_review_transition_and_notification_roll_back_together(): void
    {
        $review = $this->review(User::factory()->create());
        Event::listen(ReviewApproved::class, function () {
            throw new RuntimeException('Approval failed');
        });

        try {
            app(ProductReviewService::class)->approve($review->uuid);
            $this->fail('Expected approval rollback.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Approval failed', $exception->getMessage());
        }

        $this->assertSame(ReviewStatus::PENDING, $review->fresh()->status);
        $this->assertDatabaseCount('notification_deliveries', 0);
    }

    public function test_stock_crossing_replays_once_and_refill_allows_a_new_occurrence(): void
    {
        $this->staff('products.view');
        $product = $this->stockProduct();
        $events = [];
        Event::listen(LowStockThresholdReached::class, function (LowStockThresholdReached $event) use (&$events) {
            $events[] = $event;
        });
        $stock = app(StockService::class);

        $stock->decrementStock($product->id, null, 1); // 11 -> 10
        $stock->decrementStock($product->id, null, 1); // 10 -> 9
        $this->assertCount(1, $events);
        $this->assertSame(10, $events[0]->currentStock);
        $this->assertTrue(Str::isUuid($events[0]->occurrenceUuid));
        $replayedEvent = unserialize(serialize($events[0]));
        $this->assertSame($events[0]->occurrenceUuid, $replayedEvent->occurrenceUuid);
        $this->assertSame($events[0]->occurredAt, $replayedEvent->occurredAt);
        event($replayedEvent);
        $this->assertDatabaseCount('notification_deliveries', 1);

        $stock->incrementStock($product->id, null, 11); // 9 -> 20
        $stock->decrementStock($product->id, null, 10); // 20 -> 10
        $this->assertCount(3, $events); // crossing, explicit replay, second crossing
        $this->assertNotSame($events[0]->occurrenceUuid, $events[2]->occurrenceUuid);
        $this->assertDatabaseCount('notification_deliveries', 2);
        $this->assertSame(0, DB::table('notification_deliveries')->where('channel', 'mail')->count());
    }

    public function test_variant_crossings_are_distinct_and_target_parent_product(): void
    {
        $snapshots = [];
        $this->captureSnapshots($snapshots);
        $product = $this->stockProduct(['stock_quantity' => 100]);
        $first = ProductVariant::factory()->create(['product_id' => $product->id, 'stock_quantity' => 11]);
        $second = ProductVariant::factory()->create(['product_id' => $product->id, 'stock_quantity' => 11]);
        $stock = app(StockService::class);

        $stock->decrementStock($product->id, $first->id, 1);
        $stock->decrementStock($product->id, $first->id, 1);
        $stock->decrementStock($product->id, $second->id, 1);

        $this->assertCount(2, $snapshots);
        $this->assertNotSame($snapshots[0]->eventKey, $snapshots[1]->eventKey);
        $this->assertSame($product->uuid, $snapshots[0]->adminPayload['target_uuid']);
        $this->assertSame('product', $snapshots[0]->adminPayload['target_type']);
        $this->assertStringContainsString($first->sku, $snapshots[0]->adminPayload['message']);
        $this->assertStringContainsString($second->sku, $snapshots[1]->adminPayload['message']);
        $this->assertNull($snapshots[0]->mailRecipientKey);
        $this->assertSame(100, (int) $product->fresh()->stock_quantity);
    }

    public function test_webhook_success_is_recorded_in_transaction_once_per_order_and_targets_payment(): void
    {
        $this->staff('payments.view');
        $order = $this->createOrder();
        $this->sepayMethod();
        $initialLevel = DB::transactionLevel();
        Event::listen(PaymentSucceeded::class, function (PaymentSucceeded $event) use ($initialLevel) {
            $this->assertSame($initialLevel + 1, DB::transactionLevel());
            $this->assertSame(OrderStatus::PROCESSING, $event->order->status);
            $this->assertNotNull($event->order->paid_at);
        });

        $this->postSepay($order, 771122)->assertOk();
        $this->postSepay($order, 771122)->assertOk();
        $this->postSepay($order, 771123)->assertOk();
        $eventKey = NotificationType::PAYMENT_SUCCEEDED->value.':'.$order->uuid;
        $this->assertSame(2, $this->deliveries($eventKey)->count());
        $payment = Payment::where('order_id', $order->id)->oldest('id')->firstOrFail();
        $payload = $this->deliveries($eventKey)->where('channel', 'database')->value('payload');
        $this->assertStringContainsString($payment->uuid, $payload);
        $this->assertStringContainsString('payment', $payload);
        $this->assertStringContainsString($payment->uuid, route('admin.payments.edit', $payment->uuid));
    }

    public function test_payment_snapshot_uses_safe_order_details_not_gateway_payload_or_messages(): void
    {
        $snapshots = [];
        $this->captureSnapshots($snapshots);
        $order = $this->createOrder();
        $order->order_number = "ORD-TEST<script>\r\n";
        $payment = new Payment(['uuid' => (string) Str::uuid(), 'amount' => 250000]);
        $result = PaymentResult::success('gateway-secret', message: 'INTERNAL gateway error', rawData: [
            'admin_url' => 'https://internal.example/admin/payments', 'token' => 'secret-token',
        ]);

        PaymentSucceeded::dispatch($order, $payment, $result);

        $snapshot = $snapshots[1];
        $this->assertSame($payment->uuid, $snapshot->adminPayload['target_uuid']);
        $this->assertSame('payment', $snapshot->adminPayload['target_type']);
        $this->assertStringContainsString('250.000 VND', $snapshot->mailPayload['lines'][0]);
        $this->assertStringContainsString('ORD-TEST', $snapshot->mailPayload['lines'][0]);
        foreach (['INTERNAL', 'gateway-secret', 'secret-token', '/admin', '<script>', "\r", "\n"] as $unsafe) {
            $this->assertStringNotContainsString($unsafe, $snapshot->mailPayload['lines'][0]);
        }
    }

    public function test_review_snapshot_has_only_author_mail_and_no_admin_payload(): void
    {
        $snapshots = [];
        $this->captureSnapshots($snapshots);
        $author = User::factory()->create();
        $review = $this->review($author);
        $this->actingAs($this->staff('products.view'));

        app(ProductReviewService::class)->approve($review->uuid);

        $this->assertCount(1, $snapshots);
        $this->assertSame([], $snapshots[0]->adminPayload);
        $this->assertSame($author->email, $snapshots[0]->mailRecipient);
        $this->assertSame($author->id, $snapshots[0]->mailUserId);
        $this->assertSame('review-author:'.$review->uuid, $snapshots[0]->mailRecipientKey);
    }

    public function test_webhook_notification_failure_rolls_back_payment_and_order_changes(): void
    {
        $order = $this->createOrder();
        $this->sepayMethod();
        Event::listen(PaymentSucceeded::class, function () {
            throw new RuntimeException('Notification integration failed');
        });

        $this->postSepay($order, 991122)->assertStatus(500);

        $this->assertSame(OrderStatus::PENDING, $order->fresh()->status);
        $this->assertNull($order->fresh()->paid_at);
        $this->assertDatabaseCount('payments', 0);
        $this->assertSame(1, $order->statusHistories()->count());
        $this->assertSame(0, $this->deliveries(NotificationType::PAYMENT_SUCCEEDED->value.':'.$order->uuid)->count());
    }

    public function test_payment_failed_has_no_notification_listener(): void
    {
        $this->staff('payments.view');
        event(new PaymentFailed(null, null, PaymentResult::failed('Unverified gateway error')));

        $this->assertDatabaseCount('notification_deliveries', 0);
        Notification::assertNothingSent();
    }

    private function createOrder(?User $user = null, ?string $email = 'guest@example.test'): Order
    {
        return app(OrderService::class)->create(new OrderDTO(
            userId: $user?->id,
            paymentMethodId: null,
            shippingAddress: [
                'recipient_name' => 'Khách hàng', 'phone' => '0900000000',
                'email' => $email, 'address_line' => 'Địa chỉ giao hàng',
            ],
            items: [['product_name' => 'Sản phẩm', 'unit_price' => 250000, 'quantity' => 1]],
            subtotal: 250000,
            totalAmount: 250000,
        ));
    }

    private function staff(string $permission): User
    {
        foreach (['admin.access', $permission] as $name) {
            Permission::findOrCreate($name, 'web');
        }
        $staff = User::factory()->active()->create();
        $staff->givePermissionTo(['admin.access', $permission]);

        return $staff;
    }

    private function review(?User $author = null): ProductReview
    {
        return ProductReview::create([
            'product_id' => Product::factory()->create()->id,
            'user_id' => $author?->id, 'rating' => 5,
            'content' => 'Đánh giá sản phẩm', 'status' => ReviewStatus::PENDING,
        ]);
    }

    private function stockProduct(array $overrides = []): Product
    {
        return Product::factory()->create(array_replace([
            'stock_quantity' => 11, 'low_stock_threshold' => 10,
            'track_inventory' => true, 'allow_backorder' => false,
        ], $overrides));
    }

    private function deliveries(string $eventKey): Builder
    {
        return DB::table('notification_deliveries')->where('event_key', $eventKey);
    }

    private function captureSnapshots(array &$snapshots): void
    {
        $this->mock(NotificationService::class)->shouldReceive('record')
            ->andReturnUsing(function (NotificationEventData $event) use (&$snapshots): void {
                $snapshots[] = $event;
            });
    }

    private function sepayMethod(): void
    {
        PaymentMethod::create([
            'code' => 'sepay', 'name' => 'SePay', 'is_active' => true,
            'config' => ['api_key' => 'notification-test-key'],
        ]);
    }

    private function postSepay(Order $order, int $transactionId): TestResponse
    {
        return $this->postJson(route('api.v1.payments.webhook', 'sepay'), [
            'id' => $transactionId, 'transferAmount' => 250000,
            'content' => 'Thanh toan '.$order->order_number,
        ], ['Authorization' => 'Apikey notification-test-key']);
    }
}
