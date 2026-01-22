<?php

declare(strict_types=1);

namespace App\Shared\Specifications;

use DomainException;

/**
 * @template T
 *
 * @implements Specification<T>
 */
abstract class AbstractSpecification implements Specification
{
    protected string $failureReason = 'Specification not satisfied';

    abstract public function isSatisfiedBy(mixed $candidate): bool;

    final public function and(Specification $other): Specification
    {
        return new AndSpecification($this, $other);
    }

    final public function or(Specification $other): Specification
    {
        return new OrSpecification($this, $other);
    }

    final public function not(): Specification
    {
        return new NotSpecification($this);
    }

    final public function assertSatisfiedBy(mixed $candidate): void
    {
        if (! $this->isSatisfiedBy($candidate)) {
            throw new DomainException($this->getFailureReason());
        }
    }

    final public function getFailureReason(): string
    {
        return $this->failureReason;
    }

    protected function setFailureReason(string $reason): void
    {
        $this->failureReason = $reason;
    }
}
