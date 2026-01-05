<?php

declare(strict_types=1);

namespace App\Domain\Role\DTOs;

use App\Shared\DTOs\BaseData;

final class AssignRoleData extends BaseData
{
    public function __construct(
        public string $userId,
        public string $roleId,
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
            roleId: $data['role_id'],
        );
    }
}
