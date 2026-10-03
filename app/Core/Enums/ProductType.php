<?php

namespace App\Core\Enums;

/**
 * Loại sản phẩm: vật lý (có shipping) / ảo (không shipping) / số (download).
 *
 * Lưu ý: KHÔNG cast enum này ở model Product. DB lưu chuỗi, enum chỉ dùng ở
 * DTO/validation/label để tránh phá blade form so sánh `=== 'physical'`.
 */
enum ProductType: string
{
    case PHYSICAL = 'physical';
    case VIRTUAL = 'virtual';
    case DIGITAL = 'digital';

    public function label(): string
    {
        return match ($this) {
            self::PHYSICAL => __('Vật lý (có shipping)'),
            self::VIRTUAL => __('Ảo (không shipping)'),
            self::DIGITAL => __('Số (download)'),
        };
    }

    /**
     * Chỉ sản phẩm vật lý mới cần tính phí vận chuyển.
     */
    public function needsShipping(): bool
    {
        return $this === self::PHYSICAL;
    }
}
