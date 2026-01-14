<?php

declare(strict_types=1);

namespace App\Domain\Projections\Actions;

use App\Domain\Projections\Models\OrderFinancialProjection;
use App\Domain\Refund\Events\RefundSucceeded;

final class UpdateOrderFinancialsOnRefund
{
    public function handle(RefundSucceeded $event): void
    {
        $projection = OrderFinancialProjection::query()
            ->lockForUpdate()
            ->findOrFail($event->orderId);

        $projection->increment('refunded_amount', $event->amountCents);

        $projection->update([
            'refund_status' => $projection->refunded_amount >= $projection->total_amount
                    ? 'refunded'
                    : 'partially_refunded',
        ]);
    }
}
