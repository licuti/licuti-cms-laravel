<?php

namespace App\Repositories\Interfaces;

use App\Models\CartItem;

interface CartItemRepositoryInterface extends BaseRepositoryInterface
{
    public function findByLineKey(int $cartId, string $lineKey): ?CartItem;

    public function findByUuidInCart(int $cartId, string $uuid): ?CartItem;

    public function countByCart(int $cartId): int;
}
