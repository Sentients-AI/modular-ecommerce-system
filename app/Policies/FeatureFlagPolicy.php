<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\FeatureFlag\Models\FeatureFlag;
use App\Domain\User\Models\User;

final class FeatureFlagPolicy
{
    /**
     * Determine whether the user can view any models.
     * Admin can manage feature flags.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the model.
     * Admin can manage feature flags.
     */
    public function view(User $user, FeatureFlag $flag): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * No direct creation via UI.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * No direct editing via UI.
     */
    public function update(User $user, FeatureFlag $flag): bool
    {
        return false;
    }

    /**
     * No deletion allowed.
     */
    public function delete(User $user, FeatureFlag $flag): bool
    {
        return false;
    }

    /**
     * Toggle feature flag action.
     * Admin only can toggle feature flags.
     */
    public function toggle(User $user, FeatureFlag $flag): bool
    {
        return $user->hasRole('admin');
    }
}
