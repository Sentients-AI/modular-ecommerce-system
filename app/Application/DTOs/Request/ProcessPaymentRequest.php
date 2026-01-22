<?php

declare(strict_types=1);

namespace App\Application\DTOs\Request;

use App\Domain\Payment\ValueObjects\PaymentIntentId;

final readonly class ProcessPaymentRequest
{
    public function __construct(
        public PaymentIntentId $paymentIntentId,
    ) {}

    /**
     * Create from array data (e.g., from HTTP request).
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            paymentIntentId: PaymentIntentId::fromInt((int) $data['payment_intent_id']),
        );
    }
}
