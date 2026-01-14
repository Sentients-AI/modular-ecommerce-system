<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Payment\Actions\ConfirmPaymentIntentAction;
use App\Domain\Payment\Actions\FailPaymentIntentAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class ConfirmPaymentIntentJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120];

    public function __construct(private $intent) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        app(ConfirmPaymentIntentAction::class)
            ->execute($this->intent);
    }

    public function failed(): void
    {
        app(FailPaymentIntentAction::class)
            ->execute($this->intent);
    }
}
