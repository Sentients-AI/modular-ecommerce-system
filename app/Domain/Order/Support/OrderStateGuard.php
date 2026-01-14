<?php

declare(strict_types=1);

namespace App\Domain\Order\Support;

use App\Domain\Order\Enums\OrderStatus;
use DomainException;

final class OrderStateGuard
{
    public static function assertCanTransitionTo(OrderStatus $current, OrderStatus $target): void
    {
        if (! $current->canTransitionTo($target)) {
            throw new DomainException(
                "Invalid order state transition: {$current->value} → {$target->value}"
            );
        }
    }

    public static function canMarkPaid(OrderStatus $status): void
    {
        self::assertCanTransitionTo($status, OrderStatus::Paid);
    }

    public static function canCancel(OrderStatus $status): void
    {
        self::assertCanTransitionTo($status, OrderStatus::Cancelled);
    }

    public static function canShip(OrderStatus $status): void
    {
        self::assertCanTransitionTo($status, OrderStatus::Shipped);
    }

    public static function canDeliver(OrderStatus $status): void
    {
        self::assertCanTransitionTo($status, OrderStatus::Delivered);
    }

    public static function canFulfill(OrderStatus $status): void
    {
        self::assertCanTransitionTo($status, OrderStatus::Fulfilled);
    }

    public static function canRefund(OrderStatus $status): void
    {
        if (! $status->isRefundable()) {
            throw new DomainException(
                "Order cannot be refunded in {$status->value} state"
            );
        }
    }

    public static function canMarkFailed(OrderStatus $status): void
    {
        self::assertCanTransitionTo($status, OrderStatus::Failed);
    }
}
