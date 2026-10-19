<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'order_id',
        'payment_method_id',
        'gateway_code',
        'gateway_transaction_id',
        'status',
        'amount',
        'currency',
        'gateway_response',
        'paid_at',
    ];

    protected $casts = [
        'status'           => PaymentStatus::class,
        'amount'           => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at'          => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function isSuccess(): bool
    {
        return $this->status === PaymentStatus::SUCCESS;
    }

    public function markAsSuccess(?string $gatewayTransactionId = null, array $raw = []): void
    {
        $update = [
            'status'  => PaymentStatus::SUCCESS,
            'paid_at' => now(),
        ];

        if ($gatewayTransactionId) {
            $update['gateway_transaction_id'] = $gatewayTransactionId;
        }

        if (!empty($raw)) {
            $update['gateway_response'] = $raw;
        }

        $this->update($update);
    }

    public function markAsFailed(array $raw = []): void
    {
        $this->update([
            'status'           => PaymentStatus::FAILED,
            'gateway_response' => $raw,
        ]);
    }
}