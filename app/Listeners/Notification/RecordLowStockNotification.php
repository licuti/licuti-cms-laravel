<?php

namespace App\Listeners\Notification;

use App\DTOs\Notification\NotificationEventData;
use App\Enums\NotificationType;
use App\Events\Product\LowStockThresholdReached;
use App\Services\Shared\Notification\NotificationService;

class RecordLowStockNotification
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(LowStockThresholdReached $event): void
    {
        $values = ['sku' => $event->product->sku, 'stock' => $event->currentStock];
        $message = $event->variant
            ? __('Sản phẩm :sku, biến thể :variant đã chạm ngưỡng tồn kho, còn :stock sản phẩm.', $values + ['variant' => $event->variant->sku ?: $event->variant->uuid])
            : __('Sản phẩm :sku đã chạm ngưỡng tồn kho, còn :stock sản phẩm.', $values);

        $this->notifications->record(new NotificationEventData(
            type: NotificationType::STOCK_LOW,
            eventKey: NotificationType::STOCK_LOW->value.':'.$event->occurrenceUuid,
            occurredAt: $event->occurredAt,
            adminPayload: [
                'title' => __('Tồn kho chạm ngưỡng'),
                'message' => $message,
                'target_type' => 'product',
                'target_uuid' => $event->product->uuid,
            ],
        ));
    }
}
