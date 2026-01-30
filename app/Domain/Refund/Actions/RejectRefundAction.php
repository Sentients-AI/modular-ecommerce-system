<?php

declare(strict_types=1);

namespace App\Domain\Refund\Actions;

use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Models\Refund;
use App\Domain\User\Models\User;
use DomainException;

final class RejectRefundAction
{
    public function execute(Refund $refund, User $admin, string $reason): Refund
    {
        if (! $refund->status->canBeApproved()) {
            throw new DomainException(
                "Cannot reject refund in {$refund->status->value} state."
            );
        }

        $refund->update([
            'status' => RefundStatus::Rejected,
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'reason' => $reason,
        ]);

        return $refund;
    }
}
