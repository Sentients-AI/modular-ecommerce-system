<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Exceptions;

use DomainException;

final class InsufficientStockException extends DomainException
{
    public function __construct(
        public readonly int $productId,
        public readonly int $requested,
        public readonly int $available,
        string $message = 'Insufficient stock for product.'
    ) {
        parent::__construct($message);
    }
}
