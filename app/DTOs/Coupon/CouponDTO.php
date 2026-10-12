<?php

namespace App\DTOs\Coupon;

use App\Core\Enums\CouponType;
use Illuminate\Http\Request;

class CouponDTO
{
    public function __construct(
        public readonly string $code,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly string $type = CouponType::PERCENT->value,
        public readonly float $value = 0,
        public readonly float $minOrderValue = 0,
        public readonly ?float $maxDiscountAmount = null,
        public readonly ?int $usageLimit = null,
        public readonly ?int $usagePerUser = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly bool $isActive = true,
        public readonly bool $cannotCombineWithSaleItems = false,
        public readonly bool $stackable = false
    ) {}

    public static function fromRequest(Request $request): self
    {
        $type = $request->input('type');
        $typeValue = $type instanceof CouponType ? $type->value : (string) ($type ?? CouponType::PERCENT->value);

        return new self(
            code: strtoupper(trim((string) $request->input('code'))),
            name: $request->filled('name') ? (string) $request->input('name') : null,
            description: $request->filled('description') ? (string) $request->input('description') : null,
            type: $typeValue,
            value: (float) $request->input('value', 0),
            minOrderValue: (float) $request->input('min_order_value', 0),
            maxDiscountAmount: $request->filled('max_discount_amount') ? (float) $request->input('max_discount_amount') : null,
            usageLimit: $request->filled('usage_limit') ? (int) $request->input('usage_limit') : null,
            usagePerUser: $request->filled('usage_per_user') ? (int) $request->input('usage_per_user') : null,
            startDate: $request->filled('start_date') ? (string) $request->input('start_date') : null,
            endDate: $request->filled('end_date') ? (string) $request->input('end_date') : null,
            isActive: $request->boolean('is_active', true),
            cannotCombineWithSaleItems: $request->boolean('cannot_combine_with_sale_items', false),
            stackable: $request->boolean('stackable', false)
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'value' => $this->value,
            'min_order_value' => $this->minOrderValue,
            'max_discount_amount' => $this->maxDiscountAmount,
            'usage_limit' => $this->usageLimit,
            'usage_per_user' => $this->usagePerUser,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'is_active' => $this->isActive,
            'cannot_combine_with_sale_items' => $this->cannotCombineWithSaleItems,
            'stackable' => $this->stackable,
        ];
    }
}
