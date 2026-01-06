<?php

namespace App\Domain\Payment\Enums;

enum PaymentStatus: string
{
    case REQUIRES_PAYMENT  = 'requires_payment';
    case PROCESSING = 'processing';
    case Failed  = 'failed';
    case Cancelled = 'cancelled';
    case SUCCEEDED = 'succeeded';

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::SUCCEEDED,
            self::Failed,
            self::Cancelled,
        ], true);
    }

    public function canBeConfirmed(): bool
    {
        return $this === self::PROCESSING;
    }
}
