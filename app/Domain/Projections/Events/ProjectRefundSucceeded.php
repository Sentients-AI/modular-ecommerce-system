<?php

declare(strict_types=1);

namespace App\Domain\Projections\Events;

use App\Domain\Projections\Models\RefundProjection;
use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Events\RefundSucceeded;

final class ProjectRefundSucceeded
{
    public function handle(RefundSucceeded $event): void
    {
        RefundProjection::query()->where('refund_id', $event->refundId)
            ->update([
                'status' => RefundStatus::Succeeded->value,
                'succeeded_at' => now(),
            ]);
    }
}
