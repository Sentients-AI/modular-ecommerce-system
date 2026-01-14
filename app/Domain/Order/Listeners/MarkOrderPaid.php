<?php

declare(strict_types=1);

namespace App\Domain\Order\Listeners;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Support\OrderStateGuard;
use App\Domain\Payment\Events\PaymentSucceeded;

final class MarkOrderPaid
{
    public function handle(PaymentSucceeded $event): void
    {
        $order = Order::query()->findOrFail($event->orderId);

        OrderStateGuard::canMarkPaid($order->status);

        $order->update([
            'status' => OrderStatus::Paid,
        ]);
    }
}
