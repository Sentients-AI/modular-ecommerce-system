<?php

declare(strict_types=1);

namespace App\Domain\Cart\Exceptions;

use DomainException;

final class EmptyCartException extends DomainException
{
    public function __construct(
        string $message = 'Cannot proceed: the cart is empty.'
    ) {
        parent::__construct($message);
    }
}
