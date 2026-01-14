<?php

declare(strict_types=1);

namespace App\Domain\Refund\Listeners;

use App\Domain\Inventory\Actions\ReleaseStockAction;
use App\Domain\Inventory\DTOs\ReserveStockData;
use App\Domain\Order\Models\Order;
use App\Domain\Refund\Events\RefundSucceeded;
use App\Domain\Refund\Models\Refund;

final class ReleaseStockOnRefund
{
    public function __construct(
        private ReleaseStockAction $releaseStockAction
    ) {}

    /**
     * Release reserved stock when a refund succeeds.
     *
     * INVARIANT: Stock is only released AFTER refund is confirmed successful.
     * This prevents releasing inventory before money is actually refunded.
     */
    public function handle(RefundSucceeded $event): void
    {
        $refund = Refund::query()->findOrFail($event->refundId);
        $order = Order::query()->with('items')->findOrFail($event->orderId);

        // For full refunds, release all reserved stock
        // For partial refunds, we release proportionally based on refund percentage
        $refundPercentage = $refund->amount_cents / $order->total_cents;

        foreach ($order->items as $item) {
            // Calculate quantity to release based on refund percentage
            // For full refund (100%), release all. For 50% refund, release 50%.
            $quantityToRelease = (int) ceil($item->quantity * $refundPercentage);

            if ($quantityToRelease <= 0) {
                continue;
            }

            $this->releaseStockAction->execute(new ReserveStockData(
                productId: $item->product_id,
                quantity: $quantityToRelease,
                orderId: $order->id,
                referenceType: 'refund',
                referenceId: $refund->id,
            ));
        }
    }
}
