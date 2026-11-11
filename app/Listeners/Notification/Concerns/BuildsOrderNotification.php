<?php

namespace App\Listeners\Notification\Concerns;

use App\DTOs\Notification\NotificationEventData;
use App\Enums\NotificationType;
use App\Models\Order;

trait BuildsOrderNotification
{
    private function orderValues(Order $order, ?float $amount = null): array
    {
        $currency = preg_match('/^[A-Z]{3}$/', (string) $order->currency) ? $order->currency : 'VND';

        return [
            'number' => preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $order->order_number),
            'amount' => number_format($amount ?? (float) $order->total_amount, $currency === 'VND' ? 0 : 2, ',', '.').' '.$currency,
        ];
    }

    private function orderNotification(Order $order, NotificationType $type, array $content): NotificationEventData
    {
        $order->loadMissing(['shippingAddress', 'user']);
        $recipient = $this->validEmail($order->shippingAddress?->email)
            ?? $this->validEmail($order->user?->email);

        return new NotificationEventData(
            type: $type,
            eventKey: $type->value.':'.$order->uuid,
            occurredAt: now()->toISOString(),
            adminPayload: [
                'title' => $content['title'],
                'message' => $content['message'],
                'target_type' => $content['target_type'] ?? 'order',
                'target_uuid' => $content['target_uuid'] ?? $order->uuid,
            ],
            mailPayload: ['subject' => $content['title'], 'lines' => [$content['mail_message']]],
            mailRecipient: $recipient,
            mailRecipientKey: 'order-customer:'.$order->uuid,
            mailUserId: $order->user?->id,
        );
    }

    private function validEmail(?string $email): ?string
    {
        $email = trim($email ?? '');

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : null;
    }
}
