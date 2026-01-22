<?php

declare(strict_types=1);

namespace App\Domain\Order\Specifications;

use App\Domain\Order\Models\Order;
use App\Shared\Specifications\AbstractSpecification;

/**
 * @extends AbstractSpecification<Order>
 */
final class OrderIsRefundable extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Order) {
            $this->setFailureReason('Candidate must be an Order instance');

            return false;
        }

        if (! $candidate->status->isRefundable()) {
            $this->setFailureReason(
                "Order in {$candidate->status->value} status cannot be refunded"
            );

            return false;
        }

        return true;
    }
}
