<?php

declare(strict_types=1);

namespace App\Domain\Refund\Actions;

use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Events\RefundSucceeded;
use App\Domain\Refund\Models\Refund;
use Illuminate\Support\Facades\DB;

final class MarkRefundSucceededAction
{
    public function execute(Refund $refund, string $externalId): void
    {
        if ($refund->status !== RefundStatus::Processing) {
            return; // idempotency guard
        }

        DB::transaction(function () use ($refund, $externalId) {
            $refund->update([
                'status' => RefundStatus::Succeeded,
                'external_refund_id' => $externalId,
            ]);

            event(new RefundSucceeded(
                refundId: $refund->id,
                orderId: $refund->order_id,
                amountCents: $refund->amountCents,
                currency: 'MYR'
            ));
        });
    }
}
