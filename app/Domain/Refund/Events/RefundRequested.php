<?php

namespace App\Domain\Refund\Events;

use App\Shared\Domain\DomainEvent;

final class RefundRequested extends DomainEvent
{
    public function __construct(
        public readonly int    $refundId,
        public readonly int    $orderId,
        public readonly int    $amountCents,
        public readonly string $currency,
        public readonly string $reason,
    )
    {
        parent::__construct();
    }
}
