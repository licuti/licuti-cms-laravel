<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductAttributeValueTranslation;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\ProductAttributeRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProductAttributeRepository extends BaseRepository implements ProductAttributeRepositoryInterface
{
    const CACHE_KEY_WITH_VALUES = 'product_attributes:with_values';

    const CACHE_KEY_FOR_PRODUCT_PREFIX = 'product_attributes:for_product:';

    /**
     * Version stamp cho per-product cache keys. CACHE_STORE mặc định = database
     * → không hỗ trợ cache tags, không thể xóa hàng loạt key `for_product:*`.
     * Increment version này là cách invalidate tất cả per-product cache cùng lúc.
     */
    const CACHE_KEY_VERSION = 'product_attributes:version';

    public function __construct(ProductAttribute $model)
    {
        parent::__construct($model);
    }

    public function getActivePaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['translations', 'values.translations'])->global();

        if (! empty($filters['search'])) {
            $search = '%'.trim($filters['search']).'%';
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', $search)
                    ->orWhereHas('translations', function ($tq) use ($search) {
                        $tq->where('name', 'like', $search);
                    });
            });
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderBy('display_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByUuidWithRelations(string $uuid): ?ProductAttribute
    {
        return $this->model->with(['translations', 'values.translations'])
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    /**
     * Lấy các thuộc tính đang hoạt động kèm giá trị (dùng cho form biến thể sản phẩm / bộ lọc).
     */
    public function getActiveWithValues()
    {
        return Cache::rememberForever(self::CACHE_KEY_WITH_VALUES, function () {
            return $this->model->with(['translations', 'values.translations'])
                ->global()
                ->orderBy('display_order', 'asc')
                ->orderBy('id', 'desc')
                ->get();
        });
    }

    /**
     * Catalog toàn cục + thuộc tính custom của product $productId.
     *
     * Dùng TTL 30 ngày thay vì rememberForever: version stamp vẫn là cơ chế
     * invalidate chính, TTL chỉ dọn dọt key cũ khỏi cache store (mặc định
     * là database — không hỗ trợ tag, key phình nhanh nếu nhiều product).
     */
    public function getAvailableForProduct(int $productId)
    {
        $key = self::CACHE_KEY_FOR_PRODUCT_PREFIX.$productId.':v'.$this->attributeCacheVersion();

        return Cache::remember($key, now()->addDays(30), function () use ($productId) {
            return $this->model->with(['translations', 'values.translations'])
                ->where(function ($q) use ($productId) {
                    $q->whereNull('product_id')->orWhere('product_id', $productId);
                })
                ->orderBy('display_order', 'asc')
                ->orderBy('id', 'desc')
                ->get();
        });
    }

    private function attributeCacheVersion(): int
    {
        return (int) Cache::get(self::CACHE_KEY_VERSION, 0);
    }

    /**
     * Xóa cache catalog thuộc tính.
     *
     * Được gọi từ ProductAttributeObserver / ProductAttributeValueObserver
     * (model events) — không cần gọi tay ở service nữa.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_WITH_VALUES);
        // Increment version → mọi per-product key cũ trở nên unreachable.
        Cache::increment(self::CACHE_KEY_VERSION);
    }

    /**
     * Tìm giá trị thuộc tính theo text (attribute value là aggregate con của
     * attribute — đặt method ở đây để service không phải query model trực tiếp).
     */
    public function findValueByText(int $attributeId, string $value): ?ProductAttributeValue
    {
        return ProductAttributeValue::where('attribute_id', $attributeId)
            ->whereHas('translations', function ($q) use ($value) {
                $q->where('value', $value);
            })
            ->first();
    }

    public function createValue(int $attributeId, string $value, ?string $colorCode = null): ProductAttributeValue
    {
        $created = ProductAttributeValue::create([
            'attribute_id' => $attributeId,
            'color_code' => $colorCode,
        ]);

        $created->translations()->create([
            'locale' => app()->getLocale(),
            'value' => $value,
        ]);

        return $created;
    }

    public function deleteCascade(ProductAttribute $model): bool
    {
        $valueIds = $model->values()->pluck('id');
        ProductAttributeValueTranslation::whereIn('attribute_value_id', $valueIds)->delete();
        $model->values()->delete();
        $model->translations()->delete();

        return $this->delete($model->id);
    }
}
