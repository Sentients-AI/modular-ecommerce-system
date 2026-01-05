<?php

declare(strict_types=1);

namespace App\Domain\Role\Actions;

use App\Domain\Role\DTOs\AssignRoleData;
use App\Domain\User\Models\User;

final class AssignRoleToUser
{
    /**
     * Execute the action to assign a role to a user.
     */
    public function execute(AssignRoleData $data): User
    {
        $user = User::query()->findOrFail($data->userId);

        $user->update(['role_id' => $data->roleId]);

        return $user->fresh();
    }
}
