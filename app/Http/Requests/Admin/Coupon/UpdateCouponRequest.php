<?php

namespace App\Http\Requests\Admin\Coupon;

use App\Core\Base\BaseRequest;
use App\Core\Enums\CouponType;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('coupons.update');
    }

    public function rules(): array
    {
        $uuid = $this->route('coupon');
        $coupon = \App\Models\Coupon::where('uuid', $uuid)->firstOrFail();

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($coupon->id)],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', new Enum(CouponType::class)],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order_value' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_per_user' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
            'cannot_combine_with_sale_items' => ['boolean'],
            'stackable' => ['boolean'],
        ];
    }
}