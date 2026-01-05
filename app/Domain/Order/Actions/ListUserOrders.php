<?php

declare(strict_types=1);

namespace App\Domain\Order\Actions;

use App\Domain\Order\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListUserOrders
{
    /**
     * Execute the action to list orders for a user.
     */
    public function execute(string $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->with(['items.product', 'payment'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }
}
