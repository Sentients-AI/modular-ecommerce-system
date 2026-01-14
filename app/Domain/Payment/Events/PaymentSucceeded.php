<?php

declare(strict_types=1);

namespace App\Domain\Payment\Events;

use App\Shared\Domain\DomainEvent;

final class PaymentSucceeded extends DomainEvent
{
    public function __construct(
        public readonly int $paymentIntentId,
        public readonly int $orderId,
        public readonly int $amountCents,
        public readonly string $currency,
    ) {
        parent::__construct();
    }
}
