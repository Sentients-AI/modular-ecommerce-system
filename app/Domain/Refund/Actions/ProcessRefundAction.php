<?php

namespace App\Domain\Refund\Actions;

use App\Domain\Payment\Contracts\PaymentGatewayService;
use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Events\RefundFailed;
use App\Domain\Refund\Events\RefundSucceeded;
use App\Domain\Refund\Models\Refund;
use App\Shared\Domain\DomainEventRecorder;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ProcessRefundAction
{
    public function __construct(
        private readonly PaymentGatewayService $gateway
    )
    {
    }

    public function execute(Refund $refund): Refund
    {
        if ($refund->status !== RefundStatus::APPROVED) {
            throw new DomainException('Refund must be approved before processing.');
        }

        return DB::transaction(function () use ($refund) {
            $refund->update([
                'status' => RefundStatus::PROCESSING,
            ]);

            try {
                $this->gateway->refund(
                    paymentIntentId: $refund->payment_intent_id,
                    amountCents: $refund->amount_cents,
                );

                $refund->update([
                    'status' => RefundStatus::SUCCEEDED,
                ]);

                DomainEventRecorder::record(
                    new RefundSucceeded(
                        refundId: $refund->id,
                        orderId: $refund->order_id,
                        amountCents: $refund->amount_cents,
                        currency: $refund->currency,
                    )
                );

            } catch (Throwable $e) {
                $refund->update([
                    'status' => RefundStatus::FAILED,
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

            return $refund;
        });
    }
}
