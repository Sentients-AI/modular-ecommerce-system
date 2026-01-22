<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;
use Stringable;

abstract readonly class AbstractId implements JsonSerializable, Stringable
{
    protected function __construct(
        public int $value
    ) {
        if ($value <= 0) {
            throw new InvalidArgumentException(
                static::class.' must be a positive integer, got: '.$value
            );
        }
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    final public static function fromInt(int $value): static
    {
        return new static($value);
    }

    final public static function fromNullableInt(?int $value): ?static
    {
        return $value !== null ? static::fromInt($value) : null;
    }

    final public function equals(self $other): bool
    {
        return $this->value === $other->value && static::class === $other::class;
    }

    final public function toInt(): int
    {
        return $this->value;
    }

    final public function jsonSerialize(): int
    {
        return $this->value;
    }
}
