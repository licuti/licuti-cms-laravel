<?php

namespace App\Notifications;

use App\Models\NotificationDelivery;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SnapshotNotification extends Notification
{
    public function __construct(private readonly NotificationDelivery $delivery)
    {
        $this->id = $delivery->id;
        $this->locale = 'vi';
    }

    public function via(object $notifiable): array
    {
        return [$this->delivery->channel];
    }

    public function databaseType(object $notifiable): string
    {
        return $this->delivery->event_type->value;
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->delivery->payload ?? [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $snapshot = $this->delivery->payload ?? [];

        return (new MailMessage)->subject($snapshot['subject'])
            ->mailer(config('notification.mailer') ?? config('mail.default'))
            ->view('emails.notification', [
                'notificationSubject' => $snapshot['subject'],
                'notificationLines' => $snapshot['lines'],
                'occurredAt' => $snapshot['occurred_at'],
            ]);
    }
}
