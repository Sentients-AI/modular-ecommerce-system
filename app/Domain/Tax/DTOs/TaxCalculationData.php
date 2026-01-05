<?php

declare(strict_types=1);

namespace App\Domain\Tax\DTOs;

final class TaxCalculationData
{
    public function __construct(
        public int $subtotalCents
    ) {}
}
