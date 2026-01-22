<?php

declare(strict_types=1);

namespace App\Shared\Specifications;

/**
 * @template T
 *
 * @extends AbstractSpecification<T>
 */
final class NotSpecification extends AbstractSpecification
{
    /**
     * @param  Specification<T>  $specification
     */
    public function __construct(
        private readonly Specification $specification,
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if ($this->specification->isSatisfiedBy($candidate)) {
            $this->setFailureReason('Condition should NOT be satisfied');

            return false;
        }

        return true;
    }
}
