<?php

namespace App\Repositories\Eloquent;

use App\Enums\NotificationDeliveryStatus as Status;
use App\Models\NotificationDelivery;
use App\Repositories\Interfaces\NotificationDeliveryRepositoryInterface;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationDeliveryRepository implements NotificationDeliveryRepositoryInterface
{
    public function insert(array $attributes): ?NotificationDelivery
    {
        try {
            // A savepoint also keeps the outer transaction usable after a duplicate on PostgreSQL.
            return DB::transaction(fn () => NotificationDelivery::query()->create($attributes));
        } catch (UniqueConstraintViolationException) {
            Log::debug('notification.delivery_duplicate');

            return null;
        }
    }

    public function claim(string $deliveryId): ?NotificationDelivery
    {
        $token = (string) Str::uuid();
        $claimed = $this->due()->whereKey($deliveryId)->where('attempts', '<', $this->maxAttempts())
            ->update([
                'status' => Status::PROCESSING->value,
                // An SQL increment is necessary for competing workers to claim atomically.
                'attempts' => DB::raw('attempts + 1'),
                'lease_token' => $token,
                'lease_expires_at' => now()->addSeconds((int) config('notification.lease_seconds', 60)),
                'updated_at' => now(),
            ]);

        return $claimed ? NotificationDelivery::query()->whereKey($deliveryId)->where('lease_token', $token)->first() : null;
    }

    public function finish(NotificationDelivery $delivery, array $attributes): bool
    {
        return $this->ownedLease($delivery)->update(array_merge($attributes, [
            'lease_token' => null,
            'lease_expires_at' => null,
            'updated_at' => now(),
        ])) === 1;
    }

    public function deliverDatabase(NotificationDelivery $delivery, Closure $send): bool
    {
        return DB::transaction(function () use ($delivery, $send) {
            if (! $this->ownedLease($delivery)->lockForUpdate()->first()) {
                return false;
            }

            $send();
            // The short row lock fences this database insert and its matching ledger transition.
            $finished = $this->finish($delivery, [
                'status' => Status::SENT->value, 'sent_at' => now(), 'finished_at' => now(),
                'available_at' => null, 'last_error_code' => null,
            ]);
            if (! $finished) {
                throw new \RuntimeException('notification_database_lease_expired');
            }

            return true;
        });
    }

    public function reserveEnqueue(string $deliveryId): bool
    {
        return $this->enqueueable()->whereKey($deliveryId)->update(['last_enqueued_at' => now(), 'updated_at' => now()]) === 1;
    }

    public function recoverableIds(int $limit): array
    {
        return $this->enqueueable()->orderBy('created_at')->orderBy('id')->limit($limit)->pluck('id')->all();
    }

    public function expireExhausted(int $limit): int
    {
        $ids = $this->due()->where('attempts', '>=', $this->maxAttempts())->limit($limit)->pluck('id');

        return $this->due()->whereKey($ids)->where('attempts', '>=', $this->maxAttempts())->update([
            'status' => Status::FAILED->value, 'finished_at' => now(), 'available_at' => null,
            'lease_token' => null, 'lease_expires_at' => null, 'last_error_code' => 'attempts_exhausted',
        ]);
    }

    public function purge(): array
    {
        $batch = max(1, (int) config('notification.retention.batch', 500));
        $inbox = DatabaseNotification::query()->whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays((int) config('notification.retention.read_inbox_days', 90)));
        $inboxIds = (clone $inbox)->limit($batch)->pluck('id');
        $payloads = $this->terminalBefore((int) config('notification.retention.payload_days', 90))->whereNotNull('payload');
        $payloadIds = (clone $payloads)->limit($batch)->pluck('id');
        $deliveries = $this->terminalBefore((int) config('notification.retention.dedup_days', 365));
        $deliveryIds = (clone $deliveries)->limit($batch)->pluck('id');

        return [
            'inbox' => $inbox->whereKey($inboxIds)->delete(),
            'payloads' => $payloads->whereKey($payloadIds)->update(['payload' => null]),
            'deliveries' => $deliveries->whereKey($deliveryIds)->delete(),
        ];
    }

    private function due(): Builder
    {
        return NotificationDelivery::query()->where(function (Builder $query) {
            $query->where(fn (Builder $pending) => $pending->where('status', Status::PENDING->value)
                ->where(fn (Builder $time) => $time->whereNull('available_at')->orWhere('available_at', '<=', now()->format('Y-m-d H:i:s.u'))))
                ->orWhere(fn (Builder $processing) => $processing->where('status', Status::PROCESSING->value)
                    ->where('lease_expires_at', '<=', now()->format('Y-m-d H:i:s.u')));
        });
    }

    private function enqueueable(): Builder
    {
        return $this->due()->where('attempts', '<', $this->maxAttempts())
            ->where(fn (Builder $query) => $query->whereNull('last_enqueued_at')
                ->orWhere('last_enqueued_at', '<=', now()->subSeconds((int) config('notification.enqueue_cooldown', 300))->format('Y-m-d H:i:s.u')));
    }

    private function ownedLease(NotificationDelivery $delivery): Builder
    {
        return NotificationDelivery::query()->whereKey($delivery->id)
            ->where('status', Status::PROCESSING->value)->where('lease_token', $delivery->lease_token)
            ->where('lease_expires_at', '>', now()->format('Y-m-d H:i:s.u'));
    }

    private function terminalBefore(int $days): Builder
    {
        return NotificationDelivery::query()->whereIn('status', Status::terminalValues())
            ->where('finished_at', '<', now()->subDays($days));
    }

    private function maxAttempts(): int
    {
        return min(3, max(1, (int) config('notification.max_attempts', 3)));
    }
}
