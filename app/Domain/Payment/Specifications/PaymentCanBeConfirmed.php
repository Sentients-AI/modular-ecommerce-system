<?php

declare(strict_types=1);

namespace App\Domain\Payment\Specifications;

use App\Domain\Payment\Models\PaymentIntent;
use App\Shared\Specifications\AbstractSpecification;

/**
 * @extends AbstractSpecification<PaymentIntent>
 */
final class PaymentCanBeConfirmed extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof PaymentIntent) {
            $this->setFailureReason('Candidate must be a PaymentIntent instance');

            return false;
        }

        if (! $candidate->status->canBeConfirmed()) {
            $this->setFailureReason(
                'Payment intent cannot be confirmed from current state'
            );

            return false;
        }

        return true;
    }
}
