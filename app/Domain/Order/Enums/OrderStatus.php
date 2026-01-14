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
    case Failed = 'failed';
    case Packed = 'packed';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::AwaitingPayment, self::Paid, self::Cancelled, self::Failed], true),
            self::AwaitingPayment => in_array($target, [self::Paid, self::Cancelled, self::Failed], true),
            self::Paid => in_array($target, [self::Packed, self::Shipped, self::Cancelled, self::PartiallyRefunded, self::Refunded], true),
            self::Packed => in_array($target, [self::Shipped, self::Cancelled], true),
            self::Shipped => in_array($target, [self::Delivered, self::Fulfilled], true),
            self::Delivered => in_array($target, [self::Fulfilled, self::PartiallyRefunded, self::Refunded], true),
            self::Fulfilled => in_array($target, [self::PartiallyRefunded, self::Refunded], true),
            self::PartiallyRefunded => $target === self::Refunded,
            self::Cancelled, self::Failed, self::Refunded => false,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Cancelled, self::Failed, self::Refunded], true);
    }

    public function isRefundable(): bool
    {
        return in_array($this, [
            self::Paid,
            self::Packed,
            self::Shipped,
            self::Delivered,
            self::Fulfilled,
            self::PartiallyRefunded,
        ], true);
    }
}
