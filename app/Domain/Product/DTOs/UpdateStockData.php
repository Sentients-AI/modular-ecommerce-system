<?php

declare(strict_types=1);

namespace App\Domain\Product\DTOs;

use App\Shared\DTOs\BaseData;

final class UpdateStockData extends BaseData
{
    public function __construct(
        public string $productId,
        public int $quantity,
        public ?string $reason = null,
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
            reason: $data['reason'] ?? null,
        );
    }
}
