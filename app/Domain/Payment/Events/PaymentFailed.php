<?php

declare(strict_types=1);

namespace App\Domain\Payment\Events;

use App\Shared\Domain\DomainEvent;

final class PaymentFailed extends DomainEvent
{
    public function __construct(
        public readonly int $paymentIntentId,
        public readonly int $orderId,
        public readonly string $reason,
    ) {
        parent::__construct();
    }
}
