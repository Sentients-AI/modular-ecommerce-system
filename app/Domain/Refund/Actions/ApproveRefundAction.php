<?php

namespace App\Domain\Refund\Actions;

use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Models\Refund;
use DomainException;

final class ApproveRefundAction
{
    public function execute(Refund $refund): Refund
    {
        if ($refund->status !== RefundStatus::REQUESTED) {
            throw new DomainException('Only requested refunds can be approved.');
        }

        $refund->update([
            'status' => RefundStatus::APPROVED,
        ]);

        return $refund;
    }
}
