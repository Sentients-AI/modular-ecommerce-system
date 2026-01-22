<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Specifications;

use App\Domain\Inventory\Models\Stock;
use App\Shared\Specifications\AbstractSpecification;

/**
 * @extends AbstractSpecification<Stock>
 */
final class HasSufficientStock extends AbstractSpecification
{
    public function __construct(
        private readonly int $requestedQuantity
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Stock) {
            $this->setFailureReason('Candidate must be a Stock instance');

            return false;
        }

        if (! $candidate->isAvailable($this->requestedQuantity)) {
            $availableQuantity = $candidate->quantity_available - $candidate->quantity_reserved;
            $this->setFailureReason(
                "Insufficient stock: requested {$this->requestedQuantity}, available {$availableQuantity}"
            );

            return false;
        }

        return true;
    }
}
