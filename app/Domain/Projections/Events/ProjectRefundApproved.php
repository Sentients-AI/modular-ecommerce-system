<?php

declare(strict_types=1);

namespace App\Domain\Projections\Events;

use App\Domain\Projections\Models\RefundProjection;
use App\Domain\Refund\Enums\RefundStatus;

final class ProjectRefundApproved
{
    public function handle(RefundApproved $event): void
    {
        RefundProjection::where('refund_id', $event->refundId)
            ->update([
                'status' => RefundStatus::Approved->value,
                'approved_at' => now(),
            ]);
    }
}
