<?php

namespace App\Events\Payment;

use App\Core\Payment\DTOs\PaymentResult;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSucceeded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly Payment $payment,
        public readonly PaymentResult $result
    ) {}
}
