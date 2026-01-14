<?php

declare(strict_types=1);

namespace App\Domain\Refund\Events;

use App\Shared\Domain\DomainEvent;

final class RefundApproved extends DomainEvent
{
    public function __construct(
        public readonly int $refundId,
        public readonly int $orderId,
        public readonly int $amountCents,
        public readonly string $currency,
        public readonly int $approvedBy,
    ) {
        parent::__construct();
    }
}
