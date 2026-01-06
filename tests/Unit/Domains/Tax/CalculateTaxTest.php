<?php

declare(strict_types=1);

use App\Domain\Tax\Actions\CalculateTax;
use App\Domain\Tax\DTOs\TaxCalculationData;
use Tests\TestCase;

uses(TestCase::class);

it('tax is calculated from configured rate', function () {

    config(['tax.rate' => 0.1]);

    $tax = (new CalculateTax)->execute(
        new TaxCalculationData(10_000)
    );

    $this->assertEquals(1_000, $tax);
});
