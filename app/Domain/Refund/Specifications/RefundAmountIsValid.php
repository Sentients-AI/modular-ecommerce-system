<?php

declare(strict_types=1);

namespace App\Domain\Refund\Specifications;

use App\Domain\Order\Models\Order;
use App\Shared\Specifications\AbstractSpecification;

/**
 * @extends AbstractSpecification<Order>
 */
final class RefundAmountIsValid extends AbstractSpecification
{
    public function __construct(
        private readonly int $requestedAmountCents
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Order) {
            $this->setFailureReason('Candidate must be an Order instance');

            return false;
        }

        if ($this->requestedAmountCents <= 0) {
            $this->setFailureReason('Refund amount must be positive');

            return false;
        }

        $remainingRefundable = $candidate->getRemainingRefundableAmount();

        if ($this->requestedAmountCents > $remainingRefundable) {
            $this->setFailureReason(
                "Refund amount ({$this->requestedAmountCents}) exceeds remaining refundable amount ({$remainingRefundable})"
            );

            return false;
        }

        return true;
    }
}
