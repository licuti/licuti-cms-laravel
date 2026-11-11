<?php

namespace App\Services\Shared\Notification;

use App\DTOs\Notification\NotificationEventData;
use App\Enums\NotificationDeliveryStatus as Status;
use App\Enums\NotificationType;
use App\Jobs\DeliverNotificationJob;
use App\Repositories\Interfaces\NotificationDeliveryRepositoryInterface;
use App\Repositories\Interfaces\NotificationRecipientRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class NotificationService
{
    public function __construct(
        private readonly NotificationDeliveryRepositoryInterface $deliveries,
        private readonly NotificationRecipientRepositoryInterface $recipients,
        private readonly NotificationAccessService $access,
    ) {}

    public function record(NotificationEventData $event): void
    {
        DB::transaction(function () use ($event) {
            if ($event->adminPayload !== [] && $event->type->permission() !== null) {
                $snapshot = $this->adminSnapshot($event);
                foreach ($this->access->eligibleRecipients($event->type) as $recipient) {
                    $this->persist($event, [
                        'recipient_key' => 'user:'.$recipient->uuid,
                        'recipient_user_id' => $recipient->id, 'channel' => 'database', 'payload' => $snapshot,
                    ]);
                }
            }
            if ($event->mailRecipientKey !== null) {
                $this->recordMail($event);
            }
        });
    }

    public function recover(?int $limit = null): int
    {
        $batch = max(1, (int) config('notification.recovery_batch', 200));
        $limit = max(1, min($limit ?? $batch, $batch));
        $this->deliveries->expireExhausted($limit);
        $enqueued = 0;
        foreach ($this->deliveries->recoverableIds($limit) as $deliveryId) {
            $enqueued += (int) $this->enqueue($deliveryId);
        }

        return $enqueued;
    }

    public function purge(): array
    {
        $totals = ['inbox' => 0, 'payloads' => 0, 'deliveries' => 0];
        do {
            // Drain retention in short, bounded transactions rather than holding locks for the whole command.
            $counts = DB::transaction(fn () => $this->deliveries->purge());
            foreach ($counts as $category => $count) {
                $totals[$category] += $count;
            }
        } while (array_sum($counts) > 0);

        return $totals;
    }

    private function recordMail(NotificationEventData $event): void
    {
        $recipient = $event->mailUserId === null ? null : $this->recipients->findFresh($event->mailUserId);
        $validEmail = is_string($event->mailRecipient) && filter_var($event->mailRecipient, FILTER_VALIDATE_EMAIL) !== false;
        $missingAuthor = $event->type === NotificationType::REVIEW_APPROVED && $recipient === null;
        $skipped = ! $validEmail || $missingAuthor;
        $snapshot = $skipped ? [] : $this->mailSnapshot($event);
        $this->persist($event, [
            'recipient_key' => $event->mailRecipientKey, 'recipient_user_id' => $recipient?->id,
            'channel' => 'mail', 'payload' => array_merge($snapshot, [
                'email' => $validEmail ? $event->mailRecipient : null,
                'type' => $event->type->value, 'occurred_at' => $event->occurredAt,
            ]),
            'status' => $skipped ? Status::SKIPPED : Status::PENDING,
            'finished_at' => $skipped ? now() : null,
            'last_error_code' => $skipped ? ($missingAuthor ? 'recipient_missing' : 'invalid_email') : null,
        ]);
    }

    private function persist(NotificationEventData $event, array $attributes): void
    {
        $delivery = $this->deliveries->insert(array_merge([
            'id' => (string) Str::uuid(), 'event_key' => $event->eventKey,
            'event_type' => $event->type, 'status' => Status::PENDING, 'available_at' => now(),
        ], $attributes));
        if ($delivery !== null && $delivery->status === Status::PENDING) {
            // Laravel defers this callback until the OUTERMOST transaction commits.
            DB::afterCommit(fn () => $this->enqueue($delivery->id));
        }
    }

    private function enqueue(string $deliveryId): bool
    {
        try {
            if (! $this->deliveries->reserveEnqueue($deliveryId)) {
                return false;
            }
            Bus::dispatch(new DeliverNotificationJob($deliveryId));

            return true;
        } catch (Throwable $exception) {
            // No exception messages: queue/transport exceptions can contain credentials or payloads.
            Log::warning('notification.enqueue_failed', ['delivery_id' => $deliveryId, 'exception' => $exception::class]);

            return false;
        }
    }

    private function adminSnapshot(NotificationEventData $event): array
    {
        $snapshot = Arr::only($event->adminPayload, ['title', 'message', 'target_type', 'target_uuid']);
        foreach (['title', 'message', 'target_type', 'target_uuid'] as $key) {
            if (! isset($snapshot[$key]) || ! is_string($snapshot[$key])) {
                throw new InvalidArgumentException('Invalid admin notification snapshot.');
            }
        }
        if (! in_array($snapshot['target_type'], ['order', 'payment', 'product'], true) || ! Str::isUuid($snapshot['target_uuid'])) {
            throw new InvalidArgumentException('Invalid notification target.');
        }

        return array_merge($snapshot, ['type' => $event->type->value, 'occurred_at' => $event->occurredAt]);
    }

    private function mailSnapshot(NotificationEventData $event): array
    {
        $snapshot = Arr::only($event->mailPayload, ['subject', 'lines']);
        if (! isset($snapshot['subject'], $snapshot['lines']) || ! is_string($snapshot['subject']) || ! is_array($snapshot['lines'])) {
            throw new InvalidArgumentException('Invalid mail notification snapshot.');
        }
        foreach ($snapshot['lines'] as $line) {
            if (! is_string($line)) {
                throw new InvalidArgumentException('Notification mail lines must be plain strings.');
            }
        }
        $snapshot['subject'] = str_replace(["\r", "\n"], ' ', $snapshot['subject']);
        $snapshot['lines'] = array_values($snapshot['lines']);

        return $snapshot;
    }
}
