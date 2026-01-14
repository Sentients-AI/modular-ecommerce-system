<?php

declare(strict_types=1);

namespace App\Domain\Refund\Actions;

use App\Domain\Order\Models\Order;
use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Events\RefundRequested;
use App\Domain\Refund\Models\Refund;
use App\Shared\Domain\DomainEventRecorder;
use DomainException;
use Illuminate\Support\Facades\DB;

final class RequestRefundAction
{
    public function execute(
        Order $order,
        int $amountCents,
        string $reason
    ): Refund {
        if (! $order->isRefundable()) {
            throw new DomainException('Refund can only be requested for refundable orders.');
        }

        if ($amountCents <= 0) {
            throw new DomainException('Refund amount must be positive.');
        }

        $remainingRefundable = $order->getRemainingRefundableAmount();
        if ($amountCents > $remainingRefundable) {
            throw new DomainException(
                "Refund amount ({$amountCents}) exceeds remaining refundable amount ({$remainingRefundable})."
            );
        }

        return DB::transaction(function () use ($order, $amountCents, $reason) {
            $refund = Refund::query()->create([
                'order_id' => $order->id,
                'payment_intent_id' => $order->paymentIntent?->provider_reference,
                'amount_cents' => $amountCents,
                'currency' => $order->currency,
                'status' => RefundStatus::Requested,
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
