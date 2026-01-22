<?php

declare(strict_types=1);

namespace App\Application\DTOs\Request;

use App\Domain\Order\ValueObjects\OrderId;

final readonly class RequestRefundRequest
{
    public function __construct(
        public OrderId $orderId,
        public int $amountCents,
        public string $reason,
    ) {}

    /**
     * Create from array data (e.g., from HTTP request).
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            orderId: OrderId::fromInt((int) $data['order_id']),
            amountCents: (int) $data['amount_cents'],
            reason: $data['reason'],
        );
    }
}
