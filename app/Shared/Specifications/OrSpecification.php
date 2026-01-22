<?php

declare(strict_types=1);

namespace App\Shared\Specifications;

/**
 * @template T
 *
 * @extends AbstractSpecification<T>
 */
final class OrSpecification extends AbstractSpecification
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
        if ($this->left->isSatisfiedBy($candidate)) {
            return true;
        }

        if ($this->right->isSatisfiedBy($candidate)) {
            return true;
        }

        $this->setFailureReason(
            sprintf(
                'Neither condition satisfied: [%s] OR [%s]',
                $this->left->getFailureReason(),
                $this->right->getFailureReason()
            )
        );

        return false;
    }
}
