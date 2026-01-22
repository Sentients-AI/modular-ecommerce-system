<?php

declare(strict_types=1);

namespace App\Domain\Refund\Specifications;

use App\Domain\Refund\Models\Refund;
use App\Shared\Specifications\AbstractSpecification;

/**
 * @extends AbstractSpecification<Refund>
 */
final class RefundCanBeApproved extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Refund) {
            $this->setFailureReason('Candidate must be a Refund instance');

            return false;
        }

        if (! $candidate->status->canBeApproved()) {
            $this->setFailureReason(
                "Cannot approve refund in {$candidate->status->value} state"
            );

            return false;
        }

        return true;
    }
}
