<?php

declare(strict_types=1);

namespace App\Domain\Order\DTOs;

use App\Shared\DTOs\BaseData;

final class CreateOrderData extends BaseData
{
    /**
     * @param  array<string, mixed>|null  $shippingAddress
     * @param  array<string, mixed>|null  $billingAddress
     */
    public function __construct(
        public string|int $userId,
        public string $cartId,
        public ?array $shippingAddress = null,
        public ?array $billingAddress = null,
        public ?string $notes = null,
        public string $currency = 'USD',
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            cartId: $data['cart_id'],
            shippingAddress: $data['shipping_address'] ?? null,
            billingAddress: $data['billing_address'] ?? null,
            notes: $data['notes'] ?? null,
            currency: $data['currency'] ?? 'USD',
        );
    }
}
