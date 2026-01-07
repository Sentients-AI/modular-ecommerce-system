<?php

namespace App\Domain\Refund\Actions;

use App\Domain\Order\Models\Order;
use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Models\Refund;
use App\Domain\Refund\Events\RefundRequested;
use App\Shared\Domain\DomainEventRecorder;
use Illuminate\Support\Facades\DB;
use DomainException;

final class RequestRefundAction
{
    public function execute(
        Order  $order,
        int    $amountCents,
        string $reason
    ): Refund
    {
        if (!$order->isPaid()) {
            throw new DomainException('Refund can only be requested for paid orders.');
        }

        return DB::transaction(function () use ($order, $amountCents, $reason) {
            $refund = Refund::query()->create([
                'order_id' => $order->id,
                'payment_intent_id' => $order->payment_intent_id,
                'amount_cents' => $amountCents,
                'currency' => $order->currency,
                'status' => RefundStatus::REQUESTED,
                'reason' => $reason,
            ]);

            DomainEventRecorder::record(
                new RefundRequested(
                    refundId: $refund->id,
                    orderId: $order->id,
                    amountCents: $amountCents,
                    currency: $order->currency,
                    reason: $reason,
                )
            );

            return $refund;
        });
    }
}
