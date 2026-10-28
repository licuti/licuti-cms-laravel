<?php

namespace App\Http\Requests\Admin\FlashSale;

use App\Core\Traits\AuthorizesWithPermission;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateFlashSaleRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'flash-sales.update';
    }

    protected function prepareForValidation(): void
    {
        $uuid = $this->route('uuid') ?? $this->route('flash_sale') ?? $this->route('flashSale');
        if ($uuid) {
            $flashSale = FlashSale::where('uuid', $uuid)->first();
            if ($flashSale && $flashSale->hasStarted() && ! $this->has('starts_at')) {
                $this->merge([
                    'starts_at' => $flashSale->starts_at?->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'starts_at'                     => ['required', 'date'],
            'ends_at'                       => ['required', 'date', 'after:starts_at'],
            'is_active'                     => ['boolean'],
            'translations'                  => ['required', 'array'],
            'translations.vi.name'          => ['required', 'string', 'max:255'],
            'translations.*.name'           => ['nullable', 'string', 'max:255'],
            'translations.*.description'    => ['nullable', 'string', 'max:2000'],
            'products'                      => ['required', 'array', 'min:1'],
            'products.*.product_id'         => ['required', 'integer', 'exists:products,id'],
            'products.*.product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'products.*.flash_price'        => ['required', 'numeric', 'min:0'],
            'products.*.quantity_limit'     => ['required', 'integer', 'min:1'],
            'products.*.limit_per_user'     => ['nullable', 'integer', 'min:0'],
            'products.*.sort_order'         => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'starts_at'                     => __('Thời gian bắt đầu'),
            'ends_at'                       => __('Thời gian kết thúc'),
            'is_active'                     => __('Trạng thái kích hoạt'),
            'translations.vi.name'          => __('Tên chiến dịch (Tiếng Việt)'),
            'products'                      => __('Danh sách sản phẩm Flash Sale'),
            'products.*.flash_price'        => __('Giá Flash Sale'),
            'products.*.quantity_limit'     => __('Số lượng mở bán'),
            'products.*.limit_per_user'     => __('Giới hạn mỗi khách'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $uuid = $this->route('uuid') ?? $this->route('flash_sale') ?? $this->route('flashSale');
            $flashSale = FlashSale::with('products')->where('uuid', $uuid)->first();

            if (! $flashSale) {
                return;
            }

            // Nếu chiến dịch đã bắt đầu: không cho phép thay đổi starts_at về thời gian khác
            if ($flashSale->hasStarted()) {
                $originalStart = $flashSale->starts_at?->format('Y-m-d H:i:s');
                $newStart = date('Y-m-d H:i:s', strtotime($this->input('starts_at')));
                if ($originalStart !== $newStart) {
                    $v->errors()->add('starts_at', __('Chiến dịch Flash Sale đã bắt đầu hoặc kết thúc, không thể thay đổi thời gian bắt đầu.'));
                }
            }

            $products = $this->input('products', []);
            if (! is_array($products)) {
                return;
            }

            $seenKeys = [];
            foreach ($products as $index => $item) {
                $productId = $item['product_id'] ?? null;
                $variantId = $item['product_variant_id'] ?? null;

                if (! $productId) {
                    continue;
                }

                $key = sprintf('%d-%d', $productId, $variantId ?: 0);
                if (isset($seenKeys[$key])) {
                    $v->errors()->add("products.{$index}.product_id", __('Sản phẩm/biến thể này bị trùng lặp trong danh sách flash sale.'));
                }
                $seenKeys[$key] = true;

                // Kiểm tra flash_price < regular price
                $flashPrice = isset($item['flash_price']) ? (float) $item['flash_price'] : null;
                if ($flashPrice !== null) {
                    $regularPrice = null;
                    if ($variantId) {
                        $variant = ProductVariant::find($variantId);
                        $regularPrice = $variant ? (float) $variant->price : null;
                    }
                    if ($regularPrice === null) {
                        $product = Product::find($productId);
                        $regularPrice = $product ? (float) $product->price : null;
                    }

                    if ($regularPrice !== null && $flashPrice >= $regularPrice) {
                        $v->errors()->add("products.{$index}.flash_price", __('Giá Flash Sale (:flash) phải nhỏ hơn giá niêm yết (:regular).', [
                            'flash'   => number_format($flashPrice, 0, ',', '.') . 'đ',
                            'regular' => number_format($regularPrice, 0, ',', '.') . 'đ',
                        ]));
                    }
                }
            }
        });
    }
}