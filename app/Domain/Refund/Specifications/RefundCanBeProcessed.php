<?php

declare(strict_types=1);

namespace App\Domain\Refund\Specifications;

use App\Domain\Refund\Models\Refund;
use App\Shared\Specifications\AbstractSpecification;

/**
 * @extends AbstractSpecification<Refund>
 */
final class RefundCanBeProcessed extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Refund) {
            $this->setFailureReason('Candidate must be a Refund instance');

            return false;
        }

        if (! $candidate->status->canBeProcessed()) {
            $this->setFailureReason(
                "Cannot process refund in {$candidate->status->value} state. Refund must be approved first."
            );

            return false;
        }

        return true;
    }
}
