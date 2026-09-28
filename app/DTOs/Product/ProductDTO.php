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
        public readonly string $productType = 'physical',
        public readonly ?float $length = null,
        public readonly ?float $width = null,
        public readonly ?float $height = null,
        public readonly bool $isFreeShipping = false,
        public readonly ?float $shippingFee = null,
        public readonly ?float $taxRate = null,
        public readonly bool $isTaxInclusive = true,
        public readonly bool $allowBackorder = false,
        public readonly ?int $lowStockThreshold = null,
        public readonly ?int $minOrderQuantity = null,
        public readonly ?int $maxOrderQuantity = null,
        public readonly bool $soldIndividually = false,
        public readonly ?string $primaryImage = null,
        public readonly bool $isFeatured = false,
        public readonly bool $isActive = true,
        public readonly string $status = 'published',
        public readonly ?string $publishedAt = null,
        public readonly array $translations = [],
        public readonly array $images = [],
        public readonly array $attributes = [],
        public readonly array $newAttributes = [],
        public readonly array $variants = [],
        public readonly array $tagIds = []
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
            productType: in_array($request->input('product_type'), ['physical', 'virtual', 'digital'], true)
                ? (string) $request->input('product_type')
                : 'physical',
            length: $request->filled('length') ? (float) $request->input('length') : null,
            width: $request->filled('width') ? (float) $request->input('width') : null,
            height: $request->filled('height') ? (float) $request->input('height') : null,
            isFreeShipping: $request->boolean('is_free_shipping', false),
            shippingFee: $request->filled('shipping_fee') ? (float) $request->input('shipping_fee') : null,
            taxRate: $request->filled('tax_rate') ? (float) $request->input('tax_rate') : null,
            isTaxInclusive: $request->boolean('is_tax_inclusive', true),
            allowBackorder: $request->boolean('allow_backorder', false),
            lowStockThreshold: $request->filled('low_stock_threshold') ? (int) $request->input('low_stock_threshold') : null,
            minOrderQuantity: $request->filled('min_order_quantity') ? (int) $request->input('min_order_quantity') : null,
            maxOrderQuantity: $request->filled('max_order_quantity') ? (int) $request->input('max_order_quantity') : null,
            soldIndividually: $request->boolean('sold_individually', false),
            primaryImage: $request->filled('primary_image_uuid') ? (string) $request->input('primary_image_uuid') : null,
            isFeatured: $request->boolean('is_featured', false),
            isActive: $request->boolean('is_active', true),
            status: (string) $request->input('status', 'published'),
            publishedAt: $request->filled('published_at') ? (string) $request->input('published_at') : null,
            translations: $request->input('translations', []),
            images: self::parseImages($request),
            attributes: self::parseAttributes($request),
            newAttributes: self::parseNewAttributes($request),
            variants: self::parseVariants($request),
            tagIds: self::parseTagIds($request)
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
     * Ma trận thuộc tính sản phẩm từ form.
     *
     * tom-select submit native select: value_ids[] chứa cả id số (giá trị
     * sẵn có) lẫn text raw (giá trị gõ mới). Server phân loại tại đây:
     * - số nguyên → value_ids (id thật)
     * - chuỗi     → new_values (text, service sẽ tạo record)
     *
     * new_attributes[]: thuộc tính tùy chỉnh tạo mới cùng form (chưa có id),
     * service tạo sau khi product đã save (để scope product_id).
     */
    private static function parseAttributes(Request $request): array
    {
        $matrix = [];

        foreach ((array) $request->input('attributes', []) as $attribute) {
            if (empty($attribute['attribute_id'])) {
                continue;
            }

            $valueIds = [];
            $newValues = [];
            foreach ((array) ($attribute['value_ids'] ?? []) as $valueId) {
                if (empty($valueId)) {
                    continue;
                }

                if (preg_match('/^\d+$/', (string) $valueId)) {
                    $valueIds[] = (int) $valueId;
                } else {
                    $newValues[] = (string) $valueId;
                }
            }

            $matrix[] = [
                'attribute_id' => (int) $attribute['attribute_id'],
                'is_variation' => ! empty($attribute['is_variation']),
                'value_ids'    => array_values(array_unique($valueIds)),
                'new_values'   => array_values(array_unique($newValues)),
            ];
        }

        return $matrix;
    }

    /**
     * Thuộc tính tùy chỉnh tạo mới cùng form: new_attributes[key][name|type|values]
     */
    private static function parseNewAttributes(Request $request): array
    {
        $newAttributes = [];

        foreach ((array) $request->input('new_attributes', []) as $newAttribute) {
            $values = [];
            foreach ((array) ($newAttribute['values'] ?? []) as $value) {
                if (! empty($value)) {
                    $values[] = (string) $value;
                }
            }

            if (empty($newAttribute['name']) || empty($values)) {
                continue;
            }

            $newAttributes[] = [
                'name'         => (string) $newAttribute['name'],
                'type'         => in_array($newAttribute['type'] ?? null, ['select', 'color', 'button', 'radio'], true)
                    ? $newAttribute['type']
                    : 'select',
                'is_variation' => ! empty($newAttribute['is_variation']),
                'values'       => array_values(array_unique($values)),
            ];
        }

        return $newAttributes;
    }

    /**
     * Dữ liệu biến thể từ form: variants[key][sku|barcode|price|compare_price|
     * cost_price|stock_quantity|image_uuid|is_active]
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
                'sku'            => ! empty($variant['sku']) ? (string) $variant['sku'] : null,
                'barcode'        => ! empty($variant['barcode']) ? (string) $variant['barcode'] : null,
                'price'          => isset($variant['price']) && $variant['price'] !== '' ? (float) $variant['price'] : null,
                'compare_price'  => isset($variant['compare_price']) && $variant['compare_price'] !== '' ? (float) $variant['compare_price'] : null,
                'cost_price'     => isset($variant['cost_price']) && $variant['cost_price'] !== '' ? (float) $variant['cost_price'] : null,
                'stock_quantity' => isset($variant['stock_quantity']) && $variant['stock_quantity'] !== '' ? (int) $variant['stock_quantity'] : 0,
                'image'          => ! empty($variant['image_uuid']) ? (string) $variant['image_uuid'] : null,
                'is_active'      => isset($variant['is_active']) ? (bool) $variant['is_active'] : true,
            ];
        }

        return $variants;
    }

    /**
     * Tag từ form (tom-select multiple + create): gửi mảng `tags[]` chứa
     * id số (tag sẵn có) hoặc text (tag mới). Service phân loại & tạo mới.
     */
    private static function parseTagIds(Request $request): array
    {
        $tagIds = [];
        $newTags = [];

        foreach ((array) $request->input('tags', []) as $tag) {
            if (empty($tag)) {
                continue;
            }

            if (preg_match('/^\d+$/', (string) $tag)) {
                $tagIds[] = (int) $tag;
            } else {
                $newTags[] = (string) $tag;
            }
        }

        return [
            'ids'  => array_values(array_unique($tagIds)),
            'text' => array_values(array_unique($newTags)),
        ];
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
            'product_type' => $this->productType,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'is_free_shipping' => $this->isFreeShipping,
            'shipping_fee' => $this->shippingFee,
            'tax_rate' => $this->taxRate,
            'is_tax_inclusive' => $this->isTaxInclusive,
            'allow_backorder' => $this->allowBackorder,
            'low_stock_threshold' => $this->lowStockThreshold,
            'min_order_quantity' => $this->minOrderQuantity,
            'max_order_quantity' => $this->maxOrderQuantity,
            'sold_individually' => $this->soldIndividually,
            'primary_image' => $this->primaryImage,
            'is_featured' => $this->isFeatured,
            'is_active' => $this->isActive,
            'status' => $this->status,
            'published_at' => $this->publishedAt ?? now(),
        ];
    }
}
