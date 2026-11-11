<?php

namespace App\DTOs\Notification;

use App\Enums\NotificationType;

final readonly class NotificationEventData
{
    public function __construct(
        public NotificationType $type,
        public string $eventKey,
        public string $occurredAt,
        public array $adminPayload = [],
        public array $mailPayload = [],
        public ?string $mailRecipient = null,
        public ?string $mailRecipientKey = null,
        public ?int $mailUserId = null,
    ) {}
}
