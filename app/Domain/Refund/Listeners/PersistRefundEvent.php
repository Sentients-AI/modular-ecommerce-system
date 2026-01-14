<?php

declare(strict_types=1);

namespace App\Domain\Refund\Listeners;

use App\Domain\Refund\Models\RefundEvent;

final class PersistRefundEvent
{
    public function handle(object $event): void
    {
        if (! property_exists($event, 'refundId')) {
            return;
        }

        RefundEvent::query()->create([
            'refund_id' => $event->refundId,
            'type' => class_basename($event),
            'payload' => json_encode($event),
            'occurred_at' => now(),
        ]);
    }
}
