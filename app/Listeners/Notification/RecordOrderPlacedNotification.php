<?php

namespace App\Listeners\Notification;

use App\Enums\NotificationType;
use App\Events\Order\OrderPlaced;
use App\Listeners\Notification\Concerns\BuildsOrderNotification;
use App\Services\Shared\Notification\NotificationService;

class RecordOrderPlacedNotification
{
    use BuildsOrderNotification;

    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(OrderPlaced $event): void
    {
        $this->notifications->record($this->orderNotification($event->order, NotificationType::ORDER_PLACED, [
            'title' => __('Đơn hàng mới'),
            'message' => __('Đơn :number vừa được tạo (:amount).', $this->orderValues($event->order)),
            'mail_message' => __('Đã tiếp nhận đơn hàng :number với tổng giá trị :amount. Đây là xác nhận tạo đơn, không phải xác nhận thanh toán.', $this->orderValues($event->order)),
        ]));
    }
}
