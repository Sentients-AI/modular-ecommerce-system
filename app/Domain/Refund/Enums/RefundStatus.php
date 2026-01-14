<?php

declare(strict_types=1);

namespace App\Domain\Refund\Enums;

enum RefundStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case PendingApproval = 'pending_approval';

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Succeeded,
            self::Failed,
            self::Rejected,
            self::Cancelled,
        ], true);
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Requested => in_array($target, [self::Approved, self::Rejected, self::Cancelled], true),
            self::PendingApproval => in_array($target, [self::Approved, self::Rejected, self::Cancelled], true),
            self::Approved => in_array($target, [self::Processing, self::Cancelled], true),
            self::Processing => in_array($target, [self::Succeeded, self::Failed], true),
            self::Succeeded, self::Failed, self::Rejected, self::Cancelled => false,
        };
    }

    public function canBeApproved(): bool
    {
        return in_array($this, [self::Requested, self::PendingApproval], true);
    }

    public function canBeProcessed(): bool
    {
        return $this === self::Approved;
    }
}
