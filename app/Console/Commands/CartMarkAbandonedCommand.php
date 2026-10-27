<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Console\Command;

class CartMarkAbandonedCommand extends Command
{
    protected $signature = 'cart:mark-abandoned {--hours= : Số giờ không hoạt động để đánh dấu giỏ hàng bị bỏ quên}';

    protected $description = 'Đánh dấu các giỏ hàng đang active nhưng quá thời gian không hoạt động sang trạng thái abandoned';

    public function handle(CartRepositoryInterface $cartRepository): int
    {
        $hours = (int) ($this->option('hours') ?: config('cart.abandoned_after_hours', 24));
        $cutoff = now()->subHours($hours);

        $this->info("Đang quét các giỏ hàng không hoạt động từ trước: {$cutoff->toDateTimeString()} ({$hours}h)...");

        $affected = $cartRepository->markAbandoned($cutoff);

        $this->info("Đã đánh dấu {$affected} giỏ hàng sang trạng thái 'abandoned'.");

        return self::SUCCESS;
    }
}
