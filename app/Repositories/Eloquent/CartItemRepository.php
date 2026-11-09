<?php

namespace App\Repositories\Eloquent;

use App\Models\CartItem;
use App\Repositories\BaseRepository;
use App\Repositories\Interfaces\CartItemRepositoryInterface;

class CartItemRepository extends BaseRepository implements CartItemRepositoryInterface
{
    public function __construct(CartItem $model)
    {
        parent::__construct($model);
    }

    public function findByLineKey(int $cartId, string $lineKey): ?CartItem
    {
        return $this->model
            ->where('cart_id', $cartId)
            ->where('line_key', $lineKey)
            ->first();
    }

    public function findByUuidInCart(int $cartId, string $uuid): ?CartItem
    {
        return $this->model
            ->where('cart_id', $cartId)
            ->where('uuid', $uuid)
            ->first();
    }

    public function countByCart(int $cartId): int
    {
        return $this->model->where('cart_id', $cartId)->count();
    }
}
