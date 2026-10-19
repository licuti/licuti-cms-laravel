<?php

namespace App\Repositories\Interfaces;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePaginated(int $perPage = 15): LengthAwarePaginator;

    public function findByGatewayTransactionId(string $gatewayCode, string $transactionId): ?Payment;

    public function findPendingByOrder(int $orderId, ?string $gatewayCode = null): ?Payment;

    public function recordPayment(
        Order $order,
        string $gatewayCode,
        float $amount,
        ?string $transactionId = null,
        PaymentStatus $status = PaymentStatus::PENDING,
        array $raw = []
    ): Payment;
}
