<?php

namespace App\Listeners\Notification;

use App\Enums\NotificationType;
use App\Events\Order\OrderCancelled;
use App\Listeners\Notification\Concerns\BuildsOrderNotification;
use App\Services\Shared\Notification\NotificationService;

class RecordOrderCancelledNotification
{
    use BuildsOrderNotification;

    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(OrderCancelled $event): void
    {
        $this->notifications->record($this->orderNotification($event->order, NotificationType::ORDER_CANCELLED, [
            'title' => __('Đơn hàng đã hủy'),
            'message' => __('Đơn :number đã được hủy (:amount).', $this->orderValues($event->order)),
            'mail_message' => __('Đã ghi nhận việc hủy đơn hàng :number có tổng giá trị :amount. Vui lòng liên hệ bộ phận hỗ trợ nếu cần thêm thông tin.', $this->orderValues($event->order)),
        ]));
    }
}
