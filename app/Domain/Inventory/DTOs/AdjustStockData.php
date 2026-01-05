<?php

declare(strict_types=1);

namespace App\Domain\Inventory\DTOs;

use App\Shared\DTOs\BaseData;

final class AdjustStockData extends BaseData
{
    public function __construct(
        public string $productId,
        public int $quantityChange,
        public string $reason,
        public ?string $userId = null,
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
            quantityChange: $data['quantity_change'],
            reason: $data['reason'],
            userId: $data['user_id'] ?? null,
        );
    }
}
