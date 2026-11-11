<?php

namespace App\Jobs;

use App\Services\Shared\Notification\NotificationDeliveryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use LogicException;

class DeliverNotificationJob implements ShouldQueue
{
    use Queueable;

    // The ledger owns retry counts/backoff, including recovery after a killed worker.
    public int $tries = 1;

    public int $timeout;

    public bool $failOnTimeout = true;

    public function __construct(public readonly string $deliveryId)
    {
        $this->timeout = (int) config('notification.job_timeout', 30);
        $this->onConnection($this->asyncConnection());
        $this->onQueue(config('notification.queue', 'default'));
        $this->afterCommit();
    }

    public function handle(NotificationDeliveryService $deliveries): void
    {
        $this->assertSafeTimeouts();
        $deliveries->deliver($this->deliveryId);
    }

    private function asyncConnection(): string
    {
        $connection = config('notification.connection') ?? config('queue.default');
        $driver = config('queue.connections.'.$connection.'.driver');
        if (config('notification.connection') === null && in_array($driver, ['sync', 'deferred', 'background', 'null'], true)) {
            $connection = 'database';
            $driver = config('queue.connections.database.driver');
        }
        if (! in_array($driver, ['database', 'redis', 'sqs', 'beanstalkd'], true)) {
            throw new LogicException('Notifications require a durable asynchronous queue connection.');
        }

        return $connection;
    }

    private function assertSafeTimeouts(): void
    {
        $smtp = (int) config('notification.smtp_timeout', 15);
        $lease = (int) config('notification.lease_seconds', 60);
        $retryAfter = (int) config('queue.connections.'.$this->connection.'.retry_after', 90);
        if (! ($smtp > 0 && $smtp < $this->timeout && $this->timeout < $lease && $lease < $retryAfter)) {
            throw new LogicException('Notification timeouts must satisfy SMTP < job < lease < retry_after.');
        }
    }
}
