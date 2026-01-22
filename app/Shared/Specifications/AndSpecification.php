<?php

declare(strict_types=1);

namespace App\Shared\Specifications;

/**
 * @template T
 *
 * @extends AbstractSpecification<T>
 */
final class AndSpecification extends AbstractSpecification
{
    /**
     * @param  Specification<T>  $left
     * @param  Specification<T>  $right
     */
    public function __construct(
        private readonly Specification $left,
        private readonly Specification $right,
    ) {}

    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $this->left->isSatisfiedBy($candidate)) {
            $this->setFailureReason($this->left->getFailureReason());

            return false;
        }

        if (! $this->right->isSatisfiedBy($candidate)) {
            $this->setFailureReason($this->right->getFailureReason());

            return false;
        }

        return true;
    }
}
