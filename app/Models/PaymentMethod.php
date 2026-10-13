<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentMethod extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'code',
        'name',
        'description',
        'instructions',
        'is_active',
        'display_order',
        'min_order_amount',
        'max_order_amount',
        'supported_currencies',
        'config',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'display_order'        => 'integer',
        'min_order_amount'     => 'decimal:2',
        'max_order_amount'     => 'decimal:2',
        'supported_currencies' => 'array',
    ];

    /**
     * Tự động giải mã config (hỗ trợ tương thích ngược nếu lưu plaintext).
     */
    public function getConfigAttribute($value): ?array
    {
        if (empty($value)) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($value);
            return json_decode($decrypted, true);
        } catch (DecryptException) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : null;
        }
    }

    /**
     * Tự động mã hóa AES-256 trước khi lưu vào CSDL.
     */
    public function setConfigAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['config'] = null;
            return;
        }

        $array = is_string($value) ? json_decode($value, true) : $value;
        if (!is_array($array)) {
            $this->attributes['config'] = null;
            return;
        }

        $this->attributes['config'] = Crypt::encryptString(json_encode($array));
    }

    /**
     * Scope: Chỉ lấy các phương thức đang kích hoạt.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Sắp xếp theo thứ tự ưu tiên hiển thị.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Kiểm tra phương thức thanh toán có khả dụng cho đơn hàng không.
     */
    public function isAvailableFor(?float $amount = null, ?string $currency = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($amount !== null) {
            if ($this->min_order_amount !== null && $amount < (float) $this->min_order_amount) {
                return false;
            }

            if ($this->max_order_amount !== null && $amount > (float) $this->max_order_amount) {
                return false;
            }
        }

        if ($currency !== null && !empty($this->supported_currencies)) {
            $supported = array_map('strtoupper', (array) $this->supported_currencies);
            if (!in_array(strtoupper($currency), $supported, true)) {
                return false;
            }
        }

        return true;
    }
}