<?php

namespace App\Services\Shared\Notification;

use App\Enums\NotificationDeliveryStatus as Status;
use App\Enums\NotificationType;
use App\Models\NotificationDelivery;
use App\Notifications\SnapshotNotification;
use App\Repositories\Interfaces\NotificationDeliveryRepositoryInterface;
use App\Repositories\Interfaces\NotificationRecipientRepositoryInterface;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class NotificationDeliveryService
{
    public function __construct(
        private readonly NotificationDeliveryRepositoryInterface $deliveries,
        private readonly NotificationRecipientRepositoryInterface $recipients,
        private readonly NotificationAccessService $access,
    ) {}

    public function deliver(string $deliveryId): void
    {
        $delivery = $this->deliveries->claim($deliveryId);
        if ($delivery === null) {
            return;
        }

        try {
            match ($delivery->channel) {
                'database' => $this->deliverDatabase($delivery),
                'mail' => $this->deliverMail($delivery),
                default => $this->skip($delivery, 'unsupported_channel'),
            };
        } catch (Throwable $exception) {
            Log::warning('notification.delivery_attempt_failed', [
                'delivery_id' => $delivery->id, 'exception' => $exception::class,
            ]);
            $this->retryOrFail($delivery);
        }
    }

    private function deliverDatabase(NotificationDelivery $delivery): void
    {
        $recipient = $delivery->recipient_user_id === null ? null : $this->recipients->findFresh($delivery->recipient_user_id);
        if ($recipient === null || $delivery->recipient_key !== 'user:'.$recipient->uuid
            || ! $this->access->canReceive($recipient, $delivery->event_type)) {
            $this->skip($delivery, 'recipient_ineligible');

            return;
        }

        $this->deliveries->deliverDatabase($delivery, fn () => Notification::sendNow($recipient, new SnapshotNotification($delivery), ['database'])
        );
    }

    private function deliverMail(NotificationDelivery $delivery): void
    {
        $email = $delivery->payload['email'] ?? null;
        if (! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->skip($delivery, 'invalid_email');

            return;
        }
        if ($delivery->event_type === NotificationType::REVIEW_APPROVED
            && ($delivery->recipient_user_id === null || $this->recipients->findFresh($delivery->recipient_user_id) === null)) {
            $this->skip($delivery, 'recipient_missing');

            return;
        }

        // No transaction/row lock spans SMTP. Only this manager sees the bounded transport config.
        $this->sendMail($delivery, $email);
        if (! $this->deliveries->finish($delivery, [
            'status' => Status::SENT->value, 'sent_at' => now(), 'finished_at' => now(),
            'available_at' => null, 'last_error_code' => null,
        ])) {
            Log::warning('notification.mail_lease_lost', ['delivery_id' => $delivery->id]);
        }
    }

    private function sendMail(NotificationDelivery $delivery, string $email): void
    {
        $mailers = new NotificationMailManager(app());
        $notification = new SnapshotNotification($delivery);
        $channels = new ChannelManager(app());
        $channels->extend('mail', fn () => new MailChannel($mailers, app(Markdown::class)));
        $channels->sendNow((new AnonymousNotifiable)->route('mail', $email), $notification, ['mail']);
    }

    private function skip(NotificationDelivery $delivery, string $code): void
    {
        $this->deliveries->finish($delivery, [
            'status' => Status::SKIPPED->value, 'finished_at' => now(),
            'available_at' => null, 'last_error_code' => $code,
        ]);
    }

    private function retryOrFail(NotificationDelivery $delivery): void
    {
        $exhausted = $delivery->attempts >= min(3, max(1, (int) config('notification.max_attempts', 3)));
        $backoff = config('notification.backoff', [60, 300]);
        $this->deliveries->finish($delivery, [
            'status' => $exhausted ? Status::FAILED->value : Status::PENDING->value,
            'available_at' => $exhausted ? null : now()->addSeconds($backoff[$delivery->attempts - 1] ?? 300),
            'finished_at' => $exhausted ? now() : null,
            'last_enqueued_at' => null, 'last_error_code' => 'delivery_attempt_failed',
        ]);
    }
}
