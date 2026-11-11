<?php

namespace App\Events\Product;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Phát khi tồn kho giảm từ trên xuống bằng hoặc dưới ngưỡng cảnh báo.
 */
class LowStockThresholdReached
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public readonly string $occurrenceUuid;

    public readonly string $occurredAt;

    public function __construct(
        public readonly Product $product,
        public readonly ?ProductVariant $variant = null,
        public readonly int $currentStock = 0
    ) {
        $this->occurrenceUuid = (string) Str::uuid();
        $this->occurredAt = now()->toISOString();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.products.'.$this->product->id),
        ];
    }
}
