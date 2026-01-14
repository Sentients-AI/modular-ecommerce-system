<?php

declare(strict_types=1);

namespace App\Domain\Order\Events;

use App\Shared\Domain\DomainEvent;

final class OrderCreated extends DomainEvent
{
    public function __construct(
        public readonly int $orderId,
        public readonly int $userId,
        public readonly int $totalCents,
        public readonly string $currency
    ) {
        parent::__construct();
    }
}
