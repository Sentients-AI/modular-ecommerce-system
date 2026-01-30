<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\User\Models\User;

final class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     * Admin can view users.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     * Admin can view users.
     */
    public function view(User $user, User $model): bool
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
    public function update(User $user, User $model): bool
    {
        return false;
    }

    /**
     * No deletion allowed.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Assign role action.
     * Admin only can assign roles.
     */
    public function assignRole(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }
}
