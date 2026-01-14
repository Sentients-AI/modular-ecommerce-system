<?php

declare(strict_types=1);

namespace App\Domain\Refund\Actions;

use App\Domain\Payment\Contracts\PaymentGatewayService;
use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Events\RefundFailed;
use App\Domain\Refund\Events\RefundSucceeded;
use App\Domain\Refund\Models\Refund;
use App\Shared\Domain\DomainEventRecorder;
use DomainException;
use Throwable;

final readonly class ProcessRefundAction
{
    public function __construct(
        private PaymentGatewayService $gateway
    ) {}

    public function execute(Refund $refund): Refund
    {
        if (! $refund->status->canBeProcessed()) {
            throw new DomainException(
                "Cannot process refund in {$refund->status->value} state. Refund must be approved first."
            );
        }

        $refund->update([
            'status' => RefundStatus::Processing,
        ]);

        try {
            $this->gateway->refund(
                paymentIntentId: $refund->payment_intent_id,
                amountCents: $refund->amount_cents,
            );

            $refund->update([
                'status' => RefundStatus::Succeeded,
            ]);

            DomainEventRecorder::record(
                new RefundSucceeded(
                    refundId: $refund->id,
                    orderId: $refund->order_id,
                    amountCents: $refund->amount_cents,
                    currency: $refund->currency,
                )
            );

            return $refund;

        } catch (Throwable $e) {
            $refund->update([
                'status' => RefundStatus::Failed,
            ]);

            DomainEventRecorder::record(
                new RefundFailed(
                    refundId: $refund->id,
                    orderId: $refund->order_id,
                    reason: $e->getMessage(),
                )
            );

            throw $e;
        }
    }
}
