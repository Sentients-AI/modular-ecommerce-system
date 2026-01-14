<?php

declare(strict_types=1);

namespace App\Domain\Projections\Events;

use App\Domain\Projections\Models\RefundProjection;
use App\Domain\Refund\Enums\RefundStatus;

final class ProjectRefundCreated
{
    public function handle(RefundCreated $event): void
    {
        RefundProjection::query()->create([
            'refund_id' => $event->refundId,
            'order_id' => $event->orderId,
            'amount' => $event->amount,
            'status' => RefundStatus::PendingApproval->value,
        ]);
    }
}
