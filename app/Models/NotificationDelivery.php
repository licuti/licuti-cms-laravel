<?php

namespace App\Models;

use App\Enums\NotificationDeliveryStatus;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'id', 'event_key', 'recipient_key', 'channel', 'event_type', 'recipient_user_id',
        'payload', 'status', 'attempts', 'available_at', 'lease_token', 'lease_expires_at',
        'last_enqueued_at', 'sent_at', 'finished_at', 'last_error_code',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => NotificationType::class,
            'status' => NotificationDeliveryStatus::class,
            'payload' => 'array',
            'attempts' => 'integer',
            'available_at' => 'immutable_datetime',
            'lease_expires_at' => 'immutable_datetime',
            'last_enqueued_at' => 'immutable_datetime',
            'sent_at' => 'immutable_datetime',
            'finished_at' => 'immutable_datetime',
        ];
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }
}
