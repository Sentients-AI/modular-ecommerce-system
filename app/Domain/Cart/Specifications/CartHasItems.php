<?php

declare(strict_types=1);

namespace App\Domain\Cart\Specifications;

use App\Domain\Cart\Models\Cart;
use App\Shared\Specifications\AbstractSpecification;

/**
 * @extends AbstractSpecification<Cart>
 */
final class CartHasItems extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Cart) {
            $this->setFailureReason('Candidate must be a Cart instance');

            return false;
        }

        if ($candidate->isEmpty()) {
            $this->setFailureReason('Cart is empty');

            return false;
        }

        return true;
    }
}
