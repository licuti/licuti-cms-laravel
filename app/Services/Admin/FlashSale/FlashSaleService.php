<?php

namespace App\Services\Admin\FlashSale;

use App\Core\Base\BaseService;
use App\DTOs\FlashSale\FlashSaleDTO;
use App\DTOs\FlashSale\FlashSaleProductItemDTO;
use App\Exceptions\BusinessException;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Repositories\Interfaces\FlashSaleRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FlashSaleService extends BaseService
{
    public function __construct(
        private readonly FlashSaleRepositoryInterface $repository
    ) {}

    public function getList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getFilteredPaginated($filters, $perPage);
    }

    public function getFilterTabs(string $current = 'all'): array
    {
        $counts = $this->repository->getStatusCounts();

        return [
            [
                'label'   => __('Tất cả'),
                'key'     => 'all',
                'value'   => 'all',
                'count'   => $counts['all'] ?? 0,
                'active'  => $current === 'all',
            ],
            [
                'label'   => __('Đang diễn ra'),
                'key'     => 'running',
                'value'   => 'running',
                'count'   => $counts['running'] ?? 0,
                'active'  => $current === 'running',
            ],
            [
                'label'   => __('Sắp diễn ra'),
                'key'     => 'scheduled',
                'value'   => 'scheduled',
                'count'   => $counts['scheduled'] ?? 0,
                'active'  => $current === 'scheduled',
            ],
            [
                'label'   => __('Đã kết thúc'),
                'key'     => 'ended',
                'value'   => 'ended',
                'count'   => $counts['ended'] ?? 0,
                'active'  => $current === 'ended',
            ],
            [
                'label'   => __('Bản nháp'),
                'key'     => 'draft',
                'value'   => 'draft',
                'count'   => $counts['draft'] ?? 0,
                'active'  => $current === 'draft',
            ],
        ];
    }

    public function create(FlashSaleDTO $dto): FlashSale
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['uuid'] = Str::uuid()->toString();

            /** @var FlashSale $flashSale */
            $flashSale = $this->repository->create($data);

            $this->syncTranslations($flashSale, $dto->translations);
            $this->syncProducts($flashSale, $dto->products);

            $this->bumpCacheVersion();

            return $flashSale;
        });
    }

    public function update(string $uuid, FlashSaleDTO $dto): FlashSale
    {
        return DB::transaction(function () use ($uuid, $dto) {
            $flashSale = $this->repository->findByUuidOrFail($uuid);

            // Kiểm tra tính bất biến nếu chiến dịch đã bắt đầu
            if ($flashSale->hasStarted()) {
                $originalStart = $flashSale->starts_at?->format('Y-m-d H:i:s');
                $newStart = date('Y-m-d H:i:s', strtotime($dto->startsAt));
                if ($originalStart !== $newStart) {
                    throw new BusinessException(__('Không thể thay đổi thời gian bắt đầu của chiến dịch đã diễn ra.'), 422);
                }
            }

            $flashSale->update($dto->toArray());

            $this->syncTranslations($flashSale, $dto->translations);
            $this->syncProducts($flashSale, $dto->products, isUpdate: true);

            $this->bumpCacheVersion();

            return $flashSale->fresh(['translations', 'products.product.translations', 'products.variant']);
        });
    }

    public function delete(string $uuid): bool
    {
        return DB::transaction(function () use ($uuid) {
            $flashSale = $this->repository->findByUuidOrFail($uuid);

            if ($flashSale->isRunning()) {
                throw new BusinessException(__('Không thể xóa chiến dịch Flash Sale đang diễn ra. Vui lòng dừng kích hoạt trước.'), 422);
            }

            $deleted = (bool) $this->repository->delete($flashSale->id);
            $this->bumpCacheVersion();

            return $deleted;
        });
    }

    public function toggleStatus(string $uuid): bool
    {
        $flashSale = $this->repository->findByUuidOrFail($uuid);
        $newStatus = ! $flashSale->is_active;

        $flashSale->update(['is_active' => $newStatus]);
        $this->bumpCacheVersion();

        return $newStatus;
    }

    public function bulkDelete(array $uuids): int
    {
        return DB::transaction(function () use ($uuids) {
            $flashSales = FlashSale::whereIn('uuid', $uuids)->get();
            $ids = [];
            foreach ($flashSales as $fs) {
                if ($fs->isRunning()) {
                    throw new BusinessException(__('Chiến dịch ":name" đang diễn ra, không thể xóa hàng loạt.', [
                        'name' => $fs->name,
                    ]), 422);
                }
                $ids[] = $fs->id;
            }

            $count = $this->repository->deleteByIds($ids);
            $this->bumpCacheVersion();

            return $count;
        });
    }

    /**
     * @param array<string, array{name?: string, description?: string}> $translations
     */
    private function syncTranslations(FlashSale $flashSale, array $translations): void
    {
        foreach ($translations as $locale => $transData) {
            $name = trim($transData['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $flashSale->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'name'        => $name,
                    'description' => $transData['description'] ?? null,
                ]
            );
        }
    }

    /**
     * @param FlashSaleProductItemDTO[] $products
     */
    private function syncProducts(FlashSale $flashSale, array $products, bool $isUpdate = false): void
    {
        $incomingKeys = [];
        $existingItems = $flashSale->products()->get()->keyBy('variant_key');

        foreach ($products as $index => $itemDto) {
            $variantKey = sprintf('%d-%d', $itemDto->productId, $itemDto->productVariantId ?? 0);
            $incomingKeys[] = $variantKey;

            /** @var FlashSaleProduct|null $existing */
            $existing = $existingItems->get($variantKey);

            if ($existing) {
                // Kiểm tra nếu giảm quantity_limit nhỏ hơn sold_quantity
                if ($itemDto->quantityLimit < $existing->sold_quantity) {
                    throw new BusinessException(__('Số lượng mở bán không thể nhỏ hơn số lượng đã bán (:sold).', [
                        'sold' => $existing->sold_quantity,
                    ]), 422);
                }

                $existing->update([
                    'flash_price'    => $itemDto->flashPrice,
                    'quantity_limit' => $itemDto->quantityLimit,
                    'limit_per_user' => $itemDto->limitPerUser,
                    'sort_order'     => $itemDto->sortOrder ?: $index,
                ]);
            } else {
                $flashSale->products()->create([
                    'product_id'         => $itemDto->productId,
                    'product_variant_id' => $itemDto->productVariantId,
                    'variant_key'        => $variantKey,
                    'flash_price'        => $itemDto->flashPrice,
                    'quantity_limit'     => $itemDto->quantityLimit,
                    'sold_quantity'      => 0,
                    'limit_per_user'     => $itemDto->limitPerUser,
                    'sort_order'         => $itemDto->sortOrder ?: $index,
                ]);
            }
        }

        if ($isUpdate) {
            // Xóa các sản phẩm không còn trong danh sách
            $toRemove = $existingItems->reject(fn ($item, $key) => in_array($key, $incomingKeys, true));
            foreach ($toRemove as $item) {
                if ($item->sold_quantity > 0) {
                    throw new BusinessException(__('Không thể xóa sản phẩm đã có lượt mua trong Flash Sale.'), 422);
                }
                $item->delete();
            }
        }
    }

    private function bumpCacheVersion(): void
    {
        Cache::increment('flash_sale_index_version');
        Cache::forget('flash_sale_active_index');
    }
}
