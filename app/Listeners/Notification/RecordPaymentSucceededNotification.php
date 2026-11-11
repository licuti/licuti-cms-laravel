<?php

namespace App\Listeners\Notification;

use App\Enums\NotificationType;
use App\Events\Payment\PaymentSucceeded;
use App\Listeners\Notification\Concerns\BuildsOrderNotification;
use App\Services\Shared\Notification\NotificationService;

class RecordPaymentSucceededNotification
{
    use BuildsOrderNotification;

    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(PaymentSucceeded $event): void
    {
        $this->notifications->record($this->orderNotification($event->order, NotificationType::PAYMENT_SUCCEEDED, [
            'title' => __('Thanh toán thành công'),
            'message' => __('Đơn :number được xác nhận thanh toán :amount.', $this->orderValues($event->order, (float) $event->payment->amount)),
            'mail_message' => __('Đã ghi nhận thanh toán thành công :amount cho đơn hàng :number.', $this->orderValues($event->order, (float) $event->payment->amount)),
            'target_type' => 'payment',
            'target_uuid' => $event->payment->uuid,
        ]));
    }
}
