<?php
namespace App\Domain\Payment\Actions;
use App\Domain\Payment\Models\PaymentIntent;

final class RetryPaymentIntentAction
{
    public function execute(PaymentIntent $intent): void
    {
        if ($intent->isExpired() || $intent->attempts >= 3) {
            FailPaymentIntentAction::execute($intent);
            return;
        }

        $intent->increment('attempts');
        ConfirmPaymentIntentAction::execute($intent);
    }
}
