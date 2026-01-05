<?php

declare(strict_types=1);

namespace App\Domain\Tax\Actions;

use App\Domain\Tax\DTOs\TaxCalculationData;

final class CalculateTax
{
    public function execute(TaxCalculationData $data): int
    {
        if (! config('tax.enabled')) {
            return 0;
        }

        $rate = config('tax.rate');

        return (int) round($data->subtotalCents * $rate);
    }
}
