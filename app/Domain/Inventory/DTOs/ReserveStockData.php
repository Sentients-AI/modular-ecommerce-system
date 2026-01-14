<?php

declare(strict_types=1);

namespace App\Domain\Inventory\DTOs;

use App\Shared\DTOs\BaseData;

final class ReserveStockData extends BaseData
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public ?int $orderId = null,
        public ?string $reason = null,
        public ?string $statusReason = null,
        public ?int $referenceId = null,
        public ?string $referenceType = null,
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            quantity: $data['quantity'],
            orderId: $data['orderId'],
        );
    }
}
