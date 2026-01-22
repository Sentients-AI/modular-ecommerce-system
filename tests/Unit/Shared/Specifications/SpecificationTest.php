<?php

declare(strict_types=1);

use App\Shared\Specifications\AbstractSpecification;

// Create a simple test specification
final class IsPositiveSpecification extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! is_int($candidate)) {
            $this->setFailureReason('Candidate must be an integer');

            return false;
        }

        if ($candidate <= 0) {
            $this->setFailureReason('Number must be positive');

            return false;
        }

        return true;
    }
}

final class IsEvenSpecification extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! is_int($candidate)) {
            $this->setFailureReason('Candidate must be an integer');

            return false;
        }

        if ($candidate % 2 !== 0) {
            $this->setFailureReason('Number must be even');

            return false;
        }

        return true;
    }
}

it('returns true when specification is satisfied', function () {
    $spec = new IsPositiveSpecification;

    expect($spec->isSatisfiedBy(5))->toBeTrue();
});

it('returns false when specification is not satisfied', function () {
    $spec = new IsPositiveSpecification;

    expect($spec->isSatisfiedBy(-5))->toBeFalse();
    expect($spec->getFailureReason())->toBe('Number must be positive');
});

it('throws exception on assertSatisfiedBy when not satisfied', function () {
    $spec = new IsPositiveSpecification;

    $spec->assertSatisfiedBy(-5);
})->throws(DomainException::class, 'Number must be positive');

it('composes with AND correctly', function () {
    $spec = (new IsPositiveSpecification)->and(new IsEvenSpecification);

    expect($spec->isSatisfiedBy(4))->toBeTrue();
    expect($spec->isSatisfiedBy(3))->toBeFalse(); // positive but odd
    expect($spec->isSatisfiedBy(-2))->toBeFalse(); // even but negative
});

it('composes with OR correctly', function () {
    $spec = (new IsPositiveSpecification)->or(new IsEvenSpecification);

    expect($spec->isSatisfiedBy(4))->toBeTrue(); // both
    expect($spec->isSatisfiedBy(3))->toBeTrue(); // only positive
    expect($spec->isSatisfiedBy(-2))->toBeTrue(); // only even
    expect($spec->isSatisfiedBy(-3))->toBeFalse(); // neither
});

it('negates with NOT correctly', function () {
    $spec = (new IsPositiveSpecification)->not();

    expect($spec->isSatisfiedBy(-5))->toBeTrue();
    expect($spec->isSatisfiedBy(5))->toBeFalse();
});

it('chains multiple compositions', function () {
    // Positive AND Even, OR negative (always includes negative numbers)
    $positiveAndEven = (new IsPositiveSpecification)->and(new IsEvenSpecification);
    $isNegative = (new IsPositiveSpecification)->not();

    $spec = $positiveAndEven->or($isNegative);

    expect($spec->isSatisfiedBy(4))->toBeTrue(); // positive and even
    expect($spec->isSatisfiedBy(-5))->toBeTrue(); // negative
    expect($spec->isSatisfiedBy(3))->toBeFalse(); // positive but odd
});
