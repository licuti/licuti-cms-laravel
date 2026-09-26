<?php

namespace App\DTOs\Product;

use Illuminate\Http\Request;

class ProductDTO
{
    public function __construct(
        public readonly ?int $categoryId = null,
        public readonly ?int $brandId = null,
        public readonly ?string $sku = null,
        public readonly ?string $barcode = null,
        public readonly float $price = 0.0,
        public readonly ?float $comparePrice = null,
        public readonly ?float $costPrice = null,
        public readonly int $stockQuantity = 0,
        public readonly bool $trackInventory = true,
        public readonly ?float $weight = null,
        public readonly ?string $dimensions = null,
        public readonly ?string $primaryImage = null,
        public readonly bool $isFeatured = false,
        public readonly bool $isActive = true,
        public readonly string $status = 'published',
        public readonly ?string $publishedAt = null,
        public readonly array $translations = [],
        public readonly array $images = [],
        public readonly array $attributes = [],
        public readonly array $variants = []
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            categoryId: $request->filled('category_id') ? (int) $request->input('category_id') : null,
            brandId: $request->filled('brand_id') ? (int) $request->input('brand_id') : null,
            sku: $request->filled('sku') ? (string) $request->input('sku') : null,
            barcode: $request->filled('barcode') ? (string) $request->input('barcode') : null,
            price: (float) $request->input('price', 0),
            comparePrice: $request->filled('compare_price') ? (float) $request->input('compare_price') : null,
            costPrice: $request->filled('cost_price') ? (float) $request->input('cost_price') : null,
            stockQuantity: (int) $request->input('stock_quantity', 0),
            trackInventory: $request->boolean('track_inventory', true),
            weight: $request->filled('weight') ? (float) $request->input('weight') : null,
            dimensions: $request->filled('dimensions') ? (string) $request->input('dimensions') : null,
            primaryImage: $request->filled('primary_image_uuid') ? (string) $request->input('primary_image_uuid') : null,
            isFeatured: $request->boolean('is_featured', false),
            isActive: $request->boolean('is_active', true),
            status: (string) $request->input('status', 'published'),
            publishedAt: $request->filled('published_at') ? (string) $request->input('published_at') : null,
            translations: $request->input('translations', []),
            images: self::parseImages($request),
            attributes: self::parseAttributes($request),
            variants: self::parseVariants($request)
        );
    }

    /**
     * Đọc album ảnh từ form gallery.
     *
     * Convention của x-admin.image-upload: mỗi item gửi
     * `{name}[i][image]_uuid` (media uuid hoặc URL) và `{name}[i][image]_remove`.
     * Item bị remove hoặc chưa chọn ảnh sẽ bị loại. Ảnh đại diện là trường
     * riêng (`primary_image_uuid`), gallery giờ chỉ còn là album.
     */
    private static function parseImages(Request $request): array
    {
        $images = [];

        foreach ((array) $request->input('images', []) as $index => $img) {
            if ($request->boolean("images.{$index}.image_remove")) {
                continue;
            }

            $uuid = $request->input("images.{$index}.image_uuid");

            if (empty($uuid)) {
                continue;
            }

            $images[] = [
                'image' => $uuid,
            ];
        }

        return $images;
    }

    /**
     * Ma trận thuộc tính sản phẩm từ form: attributes[i][attribute_id|is_variation|value_ids]
     */
    private static function parseAttributes(Request $request): array
    {
        $matrix = [];

        foreach ((array) $request->input('attributes', []) as $attribute) {
            if (empty($attribute['attribute_id'])) {
                continue;
            }

            $valueIds = [];
            foreach ((array) ($attribute['value_ids'] ?? []) as $valueId) {
                if (! empty($valueId)) {
                    $valueIds[] = (int) $valueId;
                }
            }

            $matrix[] = [
                'attribute_id' => (int) $attribute['attribute_id'],
                'is_variation' => ! empty($attribute['is_variation']),
                'value_ids' => array_values(array_unique($valueIds)),
            ];
        }

        return $matrix;
    }

    /**
     * Dữ liệu biến thể từ form: variants[key][sku|price|compare_price|stock_quantity|is_active]
     * Key = sorted attribute_value ids, do JS render.
     */
    private static function parseVariants(Request $request): array
    {
        $variants = [];

        foreach ((array) $request->input('variants', []) as $key => $variant) {
            if (empty($key)) {
                continue;
            }

            $variants[$key] = [
                'sku' => ! empty($variant['sku']) ? (string) $variant['sku'] : null,
                'price' => isset($variant['price']) && $variant['price'] !== '' ? (float) $variant['price'] : null,
                'compare_price' => isset($variant['compare_price']) && $variant['compare_price'] !== '' ? (float) $variant['compare_price'] : null,
                'stock_quantity' => isset($variant['stock_quantity']) && $variant['stock_quantity'] !== '' ? (int) $variant['stock_quantity'] : 0,
                'is_active' => isset($variant['is_active']) ? (bool) $variant['is_active'] : true,
            ];
        }

        return $variants;
    }

    public function toArray(): array
    {
        return [
            'category_id' => $this->categoryId,
            'brand_id' => $this->brandId,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'price' => $this->price,
            'compare_price' => $this->comparePrice,
            'cost_price' => $this->costPrice,
            'stock_quantity' => $this->stockQuantity,
            'track_inventory' => $this->trackInventory,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
            'primary_image' => $this->primaryImage,
            'is_featured' => $this->isFeatured,
            'is_active' => $this->isActive,
            'status' => $this->status,
            'published_at' => $this->publishedAt ?? now(),
        ];
    }
}
