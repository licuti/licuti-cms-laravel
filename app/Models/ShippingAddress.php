<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingAddress extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'order_id',
        'recipient_name',
        'phone',
        'email',
        'address_line',
        'ward',
        'district',
        'province',
        'country',
        'zip_code',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line,
            $this->ward,
            $this->district,
            $this->province,
            $this->country,
        ])->filter()->implode(', ');
    }
}
