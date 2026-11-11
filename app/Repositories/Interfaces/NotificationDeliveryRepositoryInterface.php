<?php

namespace App\Repositories\Interfaces;

use App\Models\NotificationDelivery;
use Closure;

interface NotificationDeliveryRepositoryInterface
{
    public function insert(array $attributes): ?NotificationDelivery;

    public function claim(string $deliveryId): ?NotificationDelivery;

    public function finish(NotificationDelivery $delivery, array $attributes): bool;

    /** The callback must send only through Laravel's database channel. */
    public function deliverDatabase(NotificationDelivery $delivery, Closure $send): bool;

    public function reserveEnqueue(string $deliveryId): bool;

    /** @return list<string> */
    public function recoverableIds(int $limit): array;

    public function expireExhausted(int $limit): int;

    /** @return array{inbox: int, payloads: int, deliveries: int} */
    public function purge(): array;
}
