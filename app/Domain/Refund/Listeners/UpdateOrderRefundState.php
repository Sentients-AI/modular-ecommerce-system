<?php

declare(strict_types=1);

namespace App\Domain\Refund\Listeners;

use App\Domain\Order\Models\Order;
use App\Domain\Refund\Events\RefundSucceeded;

final class UpdateOrderRefundState
{
    public function handle(RefundSucceeded $event): void
    {
        $order = Order::query()->findOrFail($event->orderId);

        if ($order->getRefundedAmountCents() + $event->amountCents < $order->total_cents) {
            $order->markPartiallyRefunded($event->amountCents);
        } else {
            $order->markRefunded();
        }
    }
}
