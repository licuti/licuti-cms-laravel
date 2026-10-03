<?php

namespace App\Events\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Phát khi tồn kho chạm ngưỡng cảnh báo (low_stock_threshold).
 *
 * Groundwork cho module Notification (Tầng 7) — hiện chưa có listener.
 */
class LowStockThresholdReached
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Product $product,
        public readonly ?ProductVariant $variant = null,
        public readonly int $currentStock = 0
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.products.'.$this->product->id),
        ];
    }
}
