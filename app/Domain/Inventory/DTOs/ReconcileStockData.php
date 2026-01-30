<?php

declare(strict_types=1);

namespace App\Domain\Inventory\DTOs;

use App\Shared\DTOs\BaseData;

final class ReconcileStockData extends BaseData
{
    public function __construct(
        public string $productId,
        public int $actualCount,
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
            actualCount: $data['actual_count'],
            reason: $data['reason'],
            userId: $data['user_id'] ?? null,
        );
    }
}
