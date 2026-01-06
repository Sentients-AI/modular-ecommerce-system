<?php

declare(strict_types=1);

namespace App\Domain\Order\Enums;
enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Fulfilled = 'fulfilled';
    case Cancelled = 'cancelled';
    case AwaitingPayment = 'awaiting_payment';
    case Failed  = 'failed';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::Paid, self::Cancelled], true),
            self::Paid => in_array($target, [self::Shipped, self::Cancelled], true),
            self::Shipped => $target === self::Fulfilled,
            default => false,
        };
    }
}
