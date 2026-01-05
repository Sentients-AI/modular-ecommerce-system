<?php

declare(strict_types=1);

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Models\Cart;

final class RemoveItemFromCart
{
    /**
     * Execute the action to remove an item from the cart.
     */
    public function execute(Cart $cart, string $productId): bool
    {
        return $cart->items()
            ->where('product_id', $productId)
            ->delete() > 0;
    }
}
