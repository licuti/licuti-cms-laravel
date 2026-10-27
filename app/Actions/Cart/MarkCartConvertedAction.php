<?php

namespace App\Actions\Cart;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Models\Order;

class MarkCartConvertedAction
{
    public function execute(Cart $cart, Order $order): void
    {
        $cart->update([
            'status'             => CartStatus::CONVERTED->value,
            'converted_order_id' => $order->id,
            'last_activity_at'   => now(),
        ]);
    }
}
