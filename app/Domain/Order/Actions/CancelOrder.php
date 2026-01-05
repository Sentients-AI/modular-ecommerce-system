<?php

declare(strict_types=1);

namespace App\Domain\Order\Actions;

use App\Domain\Inventory\Actions\ReleaseStock;
use App\Domain\Inventory\DTOs\ReserveStockData;
use App\Domain\Order\Models\Order;
use Exception;
use Illuminate\Support\Facades\DB;

final class CancelOrder
{
    public function __construct(
        private readonly ReleaseStock $releaseStock,
    ) {}

    /**
     * Execute the action to cancel an order.
     *
     * @throws Exception
     */
    public function execute(Order $order): Order
    {
        if ($order->isCancelled()) {
            throw new Exception('Order is already cancelled');
        }

        if ($order->isCompleted()) {
            throw new Exception('Cannot cancel a completed order');
        }

        return DB::transaction(function () use ($order) {
            // Release reserved stock
            foreach ($order->items as $item) {
                $this->releaseStock->execute(new ReserveStockData(
                    productId: $item->product_id,
                    quantity: $item->quantity,
                    referenceType: Order::class,
                    referenceId: $order->id,
                ));
            }

            // Update order status
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return $order->fresh();
        });
    }
}
