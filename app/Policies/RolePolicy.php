<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Role\Models\Role;
use App\Domain\User\Models\User;

final class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     * Admin can view roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     * Admin can view roles.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * No direct creation via control plane.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * No direct editing via control plane.
     */
    public function update(User $user, Role $role): bool
    {
        return false;
    }

    /**
     * No deletion allowed.
     */
    public function delete(User $user, Role $role): bool
    {
        return false;
    }
}
