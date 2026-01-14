<?php

declare(strict_types=1);

namespace App\Domain\Refund\Policy;

use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Models\Refund;
use App\Domain\User\Models\User;

final class RefundPolicy
{
    public function approve(User $user, Refund $refund): bool
    {
        return $user->hasRole('Admin')
            && $refund->status === RefundStatus::PendingApproval;
    }
}
