<?php

namespace App\Console\Commands;

use App\Services\Shared\Notification\NotificationService;
use Illuminate\Console\Command;

class RecoverNotificationDeliveries extends Command
{
    protected $signature = 'notifications:recover {--limit= : Maximum jobs, capped by notification.recovery_batch}';

    protected $description = 'Enqueue due notification deliveries and close exhausted leases';

    public function handle(NotificationService $notifications): int
    {
        $limit = $this->option('limit');
        if ($limit !== null && (filter_var($limit, FILTER_VALIDATE_INT) === false || (int) $limit < 1)) {
            $this->error(__('Giới hạn phải là số nguyên dương.'));

            return self::INVALID;
        }

        $count = $notifications->recover($limit === null ? null : (int) $limit);
        $this->info(__('Đã đưa :count thông báo vào hàng đợi.', ['count' => $count]));

        return self::SUCCESS;
    }
}
