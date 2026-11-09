<?php

namespace App\Services\Admin\Cart;

use App\Core\Base\BaseService;
use App\Enums\CartStatus;
use App\Models\Cart;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Services\Shared\Cart\CartPricingService;
use Illuminate\Pagination\LengthAwarePaginator;

class CartAdminService extends BaseService
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly CartPricingService $pricingService
    ) {}

    public function getList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->cartRepository->getFilteredPaginated($filters, $perPage);
    }

    public function getDetail(string $uuid): array
    {
        /** @var Cart $cart */
        $cart = $this->cartRepository->findByUuidWithItems($uuid);
        if (!$cart) {
            abort(404, __('Không tìm thấy giỏ hàng.'));
        }

        $summary = $this->pricingService->summarize($cart);

        return [
            'cart'    => $cart,
            'summary' => $summary,
        ];
    }

    public function getStatusCounts(): array
    {
        $raw = $this->cartRepository->countByStatus();

        return [
            'all'       => array_sum($raw),
            'active'    => (int) ($raw[CartStatus::ACTIVE->value] ?? 0),
            'abandoned' => (int) ($raw[CartStatus::ABANDONED->value] ?? 0),
            'converted' => (int) ($raw[CartStatus::CONVERTED->value] ?? 0),
            'merged'    => (int) ($raw[CartStatus::MERGED->value] ?? 0),
        ];
    }

    public function delete(string $uuid): bool
    {
        $cart = $this->cartRepository->findByUuidOrNull($uuid);
        if (!$cart) {
            return false;
        }

        return (bool) $this->cartRepository->delete($cart->id);
    }

    public function bulkDelete(array $uuids): int
    {
        $carts = $this->cartRepository->findManyByUuids($uuids);
        $count = 0;

        foreach ($carts as $cart) {
            if ($cart->delete()) {
                $count++;
            }
        }

        return $count;
    }
}
