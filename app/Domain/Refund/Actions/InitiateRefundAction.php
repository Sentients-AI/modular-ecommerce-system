<?php

declare(strict_types=1);

namespace App\Domain\Refund\Actions;

use App\Domain\Order\Models\Order;
use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Models\Refund;
use DomainException;

final class InitiateRefundAction
{
    public function execute(Order $order, int $amountCents, string $reason): Refund
    {
        if (! $order->isRefundable()) {
            throw new DomainException('Order is not refundable');
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

        $paymentIntent = $order->paymentIntent;

        return Refund::query()->create([
            'order_id' => $order->id,
            'payment_intent_id' => $paymentIntent?->provider_reference ?? '',
            'amount_cents' => $amountCents,
            'currency' => $order->currency,
            'reason' => $reason,
            'status' => RefundStatus::PendingApproval,
        ]);
    }
}
