<?php

declare(strict_types=1);

namespace App\Domain\Order\Specifications;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Shared\Specifications\AbstractSpecification;

/**
 * @extends AbstractSpecification<Order>
 */
final class OrderCanBeCancelled extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Order) {
            $this->setFailureReason('Candidate must be an Order instance');

            return false;
        }

        if ($candidate->isCancelled()) {
            $this->setFailureReason('Order is already cancelled');

            return false;
        }

        if ($candidate->isCompleted()) {
            $this->setFailureReason('Cannot cancel a completed order');

            return false;
        }

        if (! $candidate->status->canTransitionTo(OrderStatus::Cancelled)) {
            $this->setFailureReason(
                "Order in {$candidate->status->value} status cannot be cancelled"
            );

            return false;
        }

        return true;
    }
}
