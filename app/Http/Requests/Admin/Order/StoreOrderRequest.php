<?php

namespace App\Http\Requests\Admin\Order;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'                 => ['nullable', 'exists:users,id'],
            'payment_method_id'       => ['nullable', 'exists:payment_methods,id'],
            'status'                  => ['nullable', Rule::enum(OrderStatus::class)],
            'recipient_name'          => ['required', 'string', 'max:100'],
            'phone'                   => ['required', 'string', 'max:20'],
            'email'                   => ['nullable', 'email', 'max:100'],
            'address_line'            => ['required', 'string', 'max:255'],
            'ward'                    => ['nullable', 'string', 'max:100'],
            'district'                => ['nullable', 'string', 'max:100'],
            'province'                => ['nullable', 'string', 'max:100'],
            'country'                 => ['nullable', 'string', 'max:10'],
            'zip_code'                => ['nullable', 'string', 'max:20'],
            'discount_amount'         => ['nullable', 'numeric', 'min:0'],
            'shipping_fee'            => ['nullable', 'numeric', 'min:0'],
            'tax_amount'              => ['nullable', 'numeric', 'min:0'],
            'notes'                   => ['nullable', 'string', 'max:1000'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.product_id'      => ['nullable', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.product_name'    => ['required', 'string', 'max:255'],
            'items.*.product_sku'     => ['nullable', 'string', 'max:100'],
            'items.*.unit_price'      => ['required', 'numeric', 'min:0'],
            'items.*.quantity'        => ['required', 'integer', 'min:1'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}