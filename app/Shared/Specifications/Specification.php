<?php

declare(strict_types=1);

namespace App\Shared\Specifications;

use DomainException;

/**
 * @template T
 */
interface Specification
{
    /**
     * Check if the specification is satisfied by the candidate.
     *
     * @param  T  $candidate
     */
    public function isSatisfiedBy(mixed $candidate): bool;

    /**
     * Combine with another specification using AND logic.
     *
     * @param  Specification<T>  $other
     * @return Specification<T>
     */
    public function and(self $other): self;

    /**
     * Combine with another specification using OR logic.
     *
     * @param  Specification<T>  $other
     * @return Specification<T>
     */
    public function or(self $other): self;

    /**
     * Negate this specification.
     *
     * @return Specification<T>
     */
    public function not(): self;

    /**
     * Assert the specification is satisfied, throwing exception if not.
     *
     * @param  T  $candidate
     *
     * @throws DomainException
     */
    public function assertSatisfiedBy(mixed $candidate): void;

    /**
     * Get the reason why the specification was not satisfied.
     */
    public function getFailureReason(): string;
}
