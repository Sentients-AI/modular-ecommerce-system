<?php

declare(strict_types=1);

namespace App\Domain\Product\DTOs;

use App\Shared\DTOs\BaseData;
use Carbon\Carbon;

final class ChangePriceData extends BaseData
{
    public function __construct(
        public int $productId,
        public int $newPriceCents,
        public ?int $newSalePrice,
        public string $reason,
        public ?Carbon $effectiveAt = null,
        public ?Carbon $expiresAt = null,
        public ?int $changedBy = null,
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
            newPriceCents: $data['new_price_cents'],
            newSalePrice: $data['new_sale_price'] ?? null,
            reason: $data['reason'],
            effectiveAt: isset($data['effective_at']) ? Carbon::parse($data['effective_at']) : null,
            expiresAt: isset($data['expires_at']) ? Carbon::parse($data['expires_at']) : null,
            changedBy: $data['changed_by'] ?? null,
        );
    }
}
