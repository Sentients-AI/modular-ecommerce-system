<?php

declare(strict_types=1);

namespace App\Domain\Cart\Actions;

use App\Domain\Cart\Models\Cart;
use InvalidArgumentException;

final class GetCart
{
    /**
     * Execute the action to get or create a cart for user or session.
     */
    public function execute(?string $userId = null, ?string $sessionId = null): Cart
    {
        if ($userId) {
            return Cart::query()
                ->with(['items.product'])
                ->firstOrCreate(['user_id' => $userId]);
        }

        if ($sessionId) {
            return Cart::query()
                ->with(['items.product'])
                ->firstOrCreate(['session_id' => $sessionId]);
        }

        throw new InvalidArgumentException('Either userId or sessionId must be provided');
    }
}
