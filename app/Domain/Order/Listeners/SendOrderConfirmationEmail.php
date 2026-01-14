<?php

declare(strict_types=1);

namespace App\Domain\Order\Listeners;

use App\Domain\Order\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SendOrderConfirmationEmail implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        // dispatch job, send mail, notify external systems

    }
}
