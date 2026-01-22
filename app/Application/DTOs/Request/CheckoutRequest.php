<?php

declare(strict_types=1);

namespace App\Application\DTOs\Request;

use App\Domain\Cart\ValueObjects\CartId;
use App\Domain\User\ValueObjects\UserId;

final readonly class CheckoutRequest
{
    public function __construct(
        public UserId $userId,
        public CartId $cartId,
        public string $currency = 'MYR',
        public ?string $idempotencyKey = null,
    ) {}

    /**
     * Create from array data (e.g., from HTTP request).
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: UserId::fromInt((int) $data['user_id']),
            cartId: CartId::fromInt((int) $data['cart_id']),
            currency: $data['currency'] ?? 'MYR',
            idempotencyKey: $data['idempotency_key'] ?? null,
        );
    }
}
