<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Refund\Models\Refund;
use App\Domain\User\Models\User;

final class RefundPolicy
{
    /**
     * Determine whether the user can view any models.
     * Support, Finance, and Admin can view refunds.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['support', 'finance', 'admin']);
    }

    /**
     * Determine whether the user can view the model.
     * Support, Finance, and Admin can view refunds.
     */
    public function view(User $user, Refund $refund): bool
    {
        return $user->hasAnyRole(['support', 'finance', 'admin']);
    }

    /**
     * No direct creation allowed in control plane.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * No direct editing allowed in control plane.
     */
    public function update(User $user, Refund $refund): bool
    {
        return false;
    }

    /**
     * No deletion allowed in control plane.
     */
    public function delete(User $user, Refund $refund): bool
    {
        return false;
    }

    /**
     * Approve refund action.
     * Finance and Admin can approve refunds.
     */
    public function approve(User $user, Refund $refund): bool
    {
        return $user->hasAnyRole(['finance', 'admin']);
    }

    /**
     * Reject refund action.
     * Finance and Admin can reject refunds.
     */
    public function reject(User $user, Refund $refund): bool
    {
        return $user->hasAnyRole(['finance', 'admin']);
    }

    /**
     * Execute refund action.
     * Finance and Admin can execute refunds.
     */
    public function execute(User $user, Refund $refund): bool
    {
        return $user->hasAnyRole(['finance', 'admin']);
    }
}
