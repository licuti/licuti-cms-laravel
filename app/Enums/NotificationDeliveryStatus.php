<?php

namespace App\Enums;

enum NotificationDeliveryStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SENT = 'sent';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';

    public static function terminalValues(): array
    {
        return [self::SENT->value, self::FAILED->value, self::SKIPPED->value];
    }
}
