<?php

namespace App\Domain\Refund\Enums;

enum RefundStatus: string
{
    case REQUESTED = 'requested';
    case APPROVED  = 'approved';
    case PROCESSING = 'processing';
    case SUCCEEDED = 'succeeded';
    case FAILED    = 'failed';
    case REJECTED  = 'rejected';

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::SUCCEEDED,
            self::FAILED,
            self::REJECTED,
        ], true);
    }
}
