<?php

declare(strict_types=1);

namespace App\Domain\Refund\Listeners;

use App\Domain\Inventory\Actions\ReleaseStockAction;
use App\Domain\Refund\Events\RefundSucceeded;

final class ReleaseStockOnRefund
{
    public function __construct(
        private ReleaseStockAction $releaseStockAction
    ) {}

    public function handle(RefundSucceeded $event): void
    {
        // TODO: Implement stock release logic based on refund
        // $this->releaseStockAction->execute(...);
    }
}
