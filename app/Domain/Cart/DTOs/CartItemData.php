<?php

declare(strict_types=1);

namespace App\Domain\Cart\DTOs;

use App\Shared\DTOs\BaseData;

final class CartItemData extends BaseData
{
    public function __construct(
        public string $productId,
        public int $quantity,
        public ?string $price = null,
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
            price: $data['price'] ?? null,
        );
    }
}
