<?php

declare(strict_types=1);

namespace App\Domain\User\Actions;

use App\Domain\User\Models\User;

final class AssignRole
{
    /**
     * Execute the action to assign a role to a user.
     */
    public function execute(User $user, string $roleId): User
    {
        $user->update(['role_id' => $roleId]);

        return $user->fresh();
    }
}
