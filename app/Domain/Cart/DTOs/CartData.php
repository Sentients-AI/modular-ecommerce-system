<?php

declare(strict_types=1);

namespace App\Domain\Cart\DTOs;

use App\Shared\DTOs\BaseData;

final class CartData extends BaseData
{
    public function __construct(
        public ?string $userId = null,
        public ?string $sessionId = null,
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            userId: $data['user_id'] ?? null,
            sessionId: $data['session_id'] ?? null,
        );
    }
}
