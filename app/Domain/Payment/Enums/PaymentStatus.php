<?php

declare(strict_types=1);

namespace App\Domain\Payment\Enums;

enum PaymentStatus: string
{
    case RequiresPayment = 'requires_payment';
    case Processing = 'processing';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Succeeded = 'succeeded';

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Succeeded,
            self::Failed,
            self::Cancelled,
        ], true);
    }

    public function canBeConfirmed(): bool
    {
        return $this === self::Processing;
    }
}
