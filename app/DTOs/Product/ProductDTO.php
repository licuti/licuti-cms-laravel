<?php

namespace App\DTOs\Product;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        public readonly bool $isFeatured = false,
        public readonly bool $isActive = true,
        public readonly string $status = 'published',
        public readonly ?string $publishedAt = null,
        public readonly array $translations = [],
        public readonly array $images = []
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
            isFeatured: $request->boolean('is_featured', false),
            isActive: $request->boolean('is_active', true),
            status: (string) $request->input('status', 'published'),
            publishedAt: $request->filled('published_at') ? (string) $request->input('published_at') : null,
            translations: $request->input('translations', []),
            images: array_values(array_filter($request->input('images', []), fn($img) => !empty($img['image'])))
        );
    }

    public function toArray(): array
    {
        return [
            'category_id'     => $this->categoryId,
            'brand_id'        => $this->brandId,
            'sku'             => $this->sku,
            'barcode'         => $this->barcode,
            'price'           => $this->price,
            'compare_price'   => $this->comparePrice,
            'cost_price'      => $this->costPrice,
            'stock_quantity'  => $this->stockQuantity,
            'track_inventory' => $this->trackInventory,
            'weight'          => $this->weight,
            'dimensions'      => $this->dimensions,
            'is_featured'     => $this->isFeatured,
            'is_active'       => $this->isActive,
            'status'          => $this->status,
            'published_at'    => $this->publishedAt ?? now(),
        ];
    }
}