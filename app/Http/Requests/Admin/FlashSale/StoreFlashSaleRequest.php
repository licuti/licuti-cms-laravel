<?php

namespace App\Http\Requests\Admin\FlashSale;

use App\Core\Traits\AuthorizesWithPermission;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFlashSaleRequest extends FormRequest
{
    use AuthorizesWithPermission;

    protected function permission(): ?string
    {
        return 'flash-sales.create';
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
            $products = $this->input('products', []);
            if (! is_array($products) || empty($products)) {
                return;
            }

            // Thu thập ID để nạp trước (Batch loading để loại bỏ N+1)
            $productIds = [];
            $variantIds = [];
            $seenKeys = [];
            $allVariantKeys = [];

            foreach ($products as $index => $item) {
                $pId = !empty($item['product_id']) ? (int) $item['product_id'] : null;
                $vId = !empty($item['product_variant_id']) ? (int) $item['product_variant_id'] : null;

                if (! $pId) {
                    continue;
                }

                $key = sprintf('%d-%d', $pId, $vId ?: 0);
                if (isset($seenKeys[$key])) {
                    $v->errors()->add("products.{$index}.product_id", __('Sản phẩm/biến thể này bị trùng lặp trong danh sách flash sale.'));
                }
                $seenKeys[$key] = true;
                $allVariantKeys[] = $key;

                $productIds[] = $pId;
                if ($vId) {
                    $variantIds[] = $vId;
                }
            }

            $productIds = array_unique($productIds);
            $variantIds = array_unique($variantIds);

            // Nạp batch (2 queries duy nhất)
            $productsMap = Product::with('translations')->whereIn('id', $productIds)->get()->keyBy('id');
            $variantsMap = !empty($variantIds)
                ? ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id')
                : collect();

            // 1. Kiểm tra giá Flash Sale phải nhỏ hơn giá niêm yết
            foreach ($products as $index => $item) {
                $pId = !empty($item['product_id']) ? (int) $item['product_id'] : null;
                $vId = !empty($item['product_variant_id']) ? (int) $item['product_variant_id'] : null;
                $flashPrice = isset($item['flash_price']) ? (float) $item['flash_price'] : null;

                if ($pId && $flashPrice !== null) {
                    $regularPrice = null;
                    if ($vId && $variant = $variantsMap->get($vId)) {
                        $regularPrice = $variant->price !== null ? (float) $variant->price : null;
                    }
                    if ($regularPrice === null && $product = $productsMap->get($pId)) {
                        $regularPrice = $product->price !== null ? (float) $product->price : null;
                    }

                    if ($regularPrice !== null && $flashPrice >= $regularPrice) {
                        $v->errors()->add("products.{$index}.flash_price", __('Giá Flash Sale (:flash) phải nhỏ hơn giá niêm yết (:regular).', [
                            'flash'   => number_format($flashPrice, 0, ',', '.') . 'đ',
                            'regular' => number_format($regularPrice, 0, ',', '.') . 'đ',
                        ]));
                    }
                }
            }

            // 2. Kiểm tra trùng lặp với chiến dịch khác đang cùng khung giờ (Overlapping campaigns)
            $startsAt = $this->input('starts_at');
            $endsAt   = $this->input('ends_at');
            $isActive = $this->boolean('is_active', true);

            if ($isActive && $startsAt && $endsAt && ! $v->errors()->has('starts_at') && ! $v->errors()->has('ends_at') && ! empty($allVariantKeys)) {
                $overlappingProducts = \App\Models\FlashSaleProduct::query()
                    ->join('flash_sales', 'flash_sales.id', '=', 'flash_sale_products.flash_sale_id')
                    ->whereNull('flash_sales.deleted_at')
                    ->where('flash_sales.is_active', true)
                    ->where('flash_sales.starts_at', '<', $endsAt)
                    ->where('flash_sales.ends_at', '>', $startsAt)
                    ->whereIn('flash_sale_products.variant_key', $allVariantKeys)
                    ->with(['flashSale.translations'])
                    ->get(['flash_sale_products.*']);

                if ($overlappingProducts->isNotEmpty()) {
                    $overlapKeyMap = $overlappingProducts->keyBy('variant_key');
                    foreach ($products as $index => $item) {
                        $pId = !empty($item['product_id']) ? (int) $item['product_id'] : null;
                        $vId = !empty($item['product_variant_id']) ? (int) $item['product_variant_id'] : null;
                        $key = sprintf('%d-%d', $pId, $vId ?: 0);

                        if ($overlapItem = $overlapKeyMap->get($key)) {
                            $campaignName = $overlapItem->flashSale?->name ?: __('Chiến dịch khác');
                            $v->errors()->add("products.{$index}.product_id", __('Sản phẩm/biến thể này đã tham gia chiến dịch ":campaign" trong cùng khung giờ.', [
                                'campaign' => $campaignName,
                            ]));
                        }
                    }
                }
            }
        });
    }
}