<?php

declare(strict_types=1);

namespace App\Application\DTOs\Response;

use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Refund\Models\Refund;
use App\Domain\Refund\ValueObjects\RefundId;

final readonly class RefundResponse
{
    public function __construct(
        public RefundId $refundId,
        public OrderId $orderId,
        public int $amountCents,
        public string $reason,
        public string $status,
    ) {}

    public static function fromRefund(Refund $refund): self
    {
        return new self(
            refundId: RefundId::fromInt($refund->id),
            orderId: OrderId::fromInt($refund->order_id),
            amountCents: $refund->amount_cents,
            reason: $refund->reason,
            status: $refund->status->value,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'refund_id' => $this->refundId->toInt(),
            'order_id' => $this->orderId->toInt(),
            'amount_cents' => $this->amountCents,
            'reason' => $this->reason,
            'status' => $this->status,
        ];
    }
}
