<?php

namespace App\Domain\Payment\DTOs;

final readonly class CreatePaymentIntentDTO
{
    public function __construct(
        public int    $orderId,
        public int    $amount, // minor units
        public string $currency,
        public string $idempotencyKey,
        public array  $metadata = [],
    ) {}
}
