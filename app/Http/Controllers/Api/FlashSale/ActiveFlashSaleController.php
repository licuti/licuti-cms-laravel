<?php

namespace App\Http\Controllers\Api\FlashSale;

use App\Core\Base\BaseController;
use App\Models\FlashSale;
use Illuminate\Http\JsonResponse;

/**
 * GET /api/v1/flash-sales/active
 *
 * Cửa hàng (storefront) cần biết chiến dịch nào đang chạy và sản phẩm nào còn suất flash
 * để hiển thị giá sốc + đồng hồ đếm ngược. Trả về dữ liệu ĐANG HIỆU LỰC tại thời điểm gọi,
 * không phải snapshot: giá flash được tính lại lúc checkout nên storefront chỉ dùng số này để hiển thị.
 */
class ActiveFlashSaleController extends BaseController
{
    public function __invoke(): JsonResponse
    {
        $flashSales = FlashSale::query()
            ->activeWindow()
            ->with([
                'translations',
                'products' => fn ($q) => $q->with(['product.translations', 'variant'])->orderBy('sort_order'),
            ])
            ->orderBy('ends_at')
            ->get();

        $payload = $flashSales->map(function (FlashSale $sale) {
            return [
                'uuid'      => $sale->uuid,
                'name'      => $sale->name,
                'starts_at' => $sale->starts_at?->toIso8601String(),
                'ends_at'   => $sale->ends_at?->toIso8601String(),
                'products'  => $sale->products
                    ->filter(fn ($item) => $item->product !== null)
                    ->map(function ($item) use ($sale) {
                        $regularPrice = $item->variant?->price ?? $item->product->price;

                        return [
                            'flash_sale_product_id' => $item->id,
                            'product_uuid'          => $item->product->uuid,
                            'variant_uuid'          => $item->variant?->uuid,
                            'name'                  => $item->product->name,
                            'sku'                   => $item->variant?->sku ?? $item->product->sku,
                            'regular_price'         => (float) $regularPrice,
                            'flash_price'           => (float) $item->flash_price,
                            'discount_percent'      => (float) $regularPrice > 0
                                ? round(((float) $regularPrice - (float) $item->flash_price) / (float) $regularPrice * 100, 1)
                                : 0,
                            'quantity_limit'        => $item->quantity_limit === null ? null : (int) $item->quantity_limit,
                            'sold_quantity'         => (int) $item->sold_quantity,
                            'remaining_quantity'    => $item->quantity_limit === null ? null : $item->remaining_quantity,
                            'limit_per_user'        => $item->limit_per_user === null ? null : (int) $item->limit_per_user,
                            'sold_out'              => $item->is_sold_out,
                            'ends_at'               => $sale->ends_at?->toIso8601String(),
                        ];
                    })
                    ->values(),
            ];
        })->values();

        return $this->successResponse(
            data: [
                'flash_sales' => $payload,
                'count'       => $payload->count(),
            ],
            message: 'Danh sách Flash Sale đang diễn ra'
        );
    }
}
