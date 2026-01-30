<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Order\Models\Order;
use App\Domain\User\Models\User;

final class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     * Support, Finance, and Admin can view orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['support', 'finance', 'admin']);
    }

    /**
     * Determine whether the user can view the model.
     * Support, Finance, and Admin can view orders.
     */
    public function view(User $user, Order $order): bool
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
    public function update(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * No deletion allowed in control plane.
     */
    public function delete(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Cancel order action.
     * Support and Admin can cancel orders.
     */
    public function cancel(User $user, Order $order): bool
    {
        return $user->hasAnyRole(['support', 'admin']);
    }

    /**
     * Retry payment action.
     * Support and Admin can retry payments.
     */
    public function retryPayment(User $user, Order $order): bool
    {
        return $user->hasAnyRole(['support', 'admin']);
    }

    /**
     * Mark as fraudulent action.
     * Admin only.
     */
    public function markAsFraudulent(User $user, Order $order): bool
    {
        return $user->hasRole('admin');
    }
}
