<?php

declare(strict_types=1);

namespace App\Domain\User\DTOs;

use App\Shared\DTOs\BaseData;

final class RegisterUserData extends BaseData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?string $roleId = null,
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            roleId: $data['role_id'] ?? null,
        );
    }
}
