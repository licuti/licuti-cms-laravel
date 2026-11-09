<?php

namespace App\Console\Commands;

use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Console\Command;

class CartPurgeCommand extends Command
{
    protected $signature = 'cart:purge 
                            {--guest-days= : Số ngày lưu vết trước khi xóa giỏ hàng guest (mặc định theo config)}
                            {--user-days= : Số ngày lưu vết trước khi xóa giỏ hàng user (mặc định theo config)}';

    protected $description = 'Dọn dẹp và xóa vĩnh viễn các giỏ hàng cũ (abandoned, merged, converted) quá hạn';

    public function handle(CartRepositoryInterface $cartRepository): int
    {
        $guestDays = (int) ($this->option('guest-days') ?: config('cart.purge_guest_after_days', 30));
        $userDays = (int) ($this->option('user-days') ?: config('cart.purge_user_after_days', 90));

        $guestThreshold = now()->subDays($guestDays);
        $userThreshold = now()->subDays($userDays);

        $this->info("Đang dọn dẹp các giỏ hàng cũ (Guest > {$guestDays} ngày, User > {$userDays} ngày)...");

        $deleted = $cartRepository->purgeStale($guestThreshold, $userThreshold);

        $this->info("Đã dọn dẹp thành công {$deleted} giỏ hàng cũ.");

        return self::SUCCESS;
    }
}
