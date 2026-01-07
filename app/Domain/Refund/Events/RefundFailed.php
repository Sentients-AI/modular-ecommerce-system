<?php

namespace App\Domain\Refund\Events;

use App\Shared\Domain\DomainEvent;

final class RefundFailed extends DomainEvent
{
    public function __construct(
        public readonly int    $refundId,
        public readonly int    $orderId,
        public readonly string $reason,
    )
    {
        parent::__construct();
    }
}
