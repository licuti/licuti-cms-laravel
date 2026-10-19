<?php

namespace App\Repositories\Eloquent;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['order', 'paymentMethod'])
            ->latest('id')
            ->paginate($perPage);
    }

    public function findByGatewayTransactionId(string $gatewayCode, string $transactionId): ?Payment
    {
        return $this->model->newQuery()
            ->where('gateway_code', $gatewayCode)
            ->where('gateway_transaction_id', $transactionId)
            ->first();
    }

    public function findPendingByOrder(int $orderId, ?string $gatewayCode = null): ?Payment
    {
        $query = $this->model->newQuery()
            ->where('order_id', $orderId)
            ->where('status', PaymentStatus::PENDING);

        if ($gatewayCode) {
            $query->where('gateway_code', $gatewayCode);
        }

        return $query->latest('id')->first();
    }

    public function recordPayment(
        Order $order,
        string $gatewayCode,
        float $amount,
        ?string $transactionId = null,
        PaymentStatus $status = PaymentStatus::PENDING,
        array $raw = []
    ): Payment {
        return $this->create([
            'order_id'               => $order->id,
            'payment_method_id'      => $order->payment_method_id,
            'gateway_code'           => $gatewayCode,
            'gateway_transaction_id' => $transactionId,
            'status'                 => $status,
            'amount'                 => $amount,
            'currency'               => $order->currency ?? 'VND',
            'gateway_response'       => $raw,
            'paid_at'                => $status === PaymentStatus::SUCCESS ? now() : null,
        ]);
    }
}
