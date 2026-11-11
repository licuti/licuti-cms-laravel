<?php

namespace App\Console\Commands;

use App\Services\Shared\Notification\NotificationService;
use Illuminate\Console\Command;

class PurgeNotificationDeliveries extends Command
{
    protected $signature = 'notifications:purge';

    protected $description = 'Purge old read notifications, terminal payloads and expired deduplication keys in bounded batches';

    public function handle(NotificationService $notifications): int
    {
        $counts = $notifications->purge();
        $this->info(__('Đã dọn :inbox thông báo đã đọc, :payloads nội dung và :deliveries khóa hết hạn.', $counts));

        return self::SUCCESS;
    }
}
