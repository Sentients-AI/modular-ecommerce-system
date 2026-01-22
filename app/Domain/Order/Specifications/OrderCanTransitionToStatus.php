<?php

declare(strict_types=1);

namespace App\Domain\Order\Specifications;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Shared\Specifications\AbstractSpecification;

/**
 * @extends AbstractSpecification<Order>
 */
final class OrderCanTransitionToStatus extends AbstractSpecification
{
    public function __construct(
        private readonly OrderStatus $targetStatus
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Order) {
            $this->setFailureReason('Candidate must be an Order instance');

            return false;
        }

        if (! $candidate->status->canTransitionTo($this->targetStatus)) {
            $this->setFailureReason(
                "Order cannot transition from {$candidate->status->value} to {$this->targetStatus->value}"
            );

            return false;
        }

        return true;
    }
}
