<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Contracts\PaymentGatewayService;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntent;
use Illuminate\Support\Facades\DB;
use DomainException;
use Throwable;

final readonly class ConfirmPaymentIntentAction
{
    public function __construct(
        private PaymentGatewayService $gateway
    ) {}

    public function execute(PaymentIntent $intent): PaymentIntent
    {
        if (! $intent->status->canBeConfirmed()) {
            throw new DomainException('Payment intent cannot be confirmed from current state.');
        }

        return DB::transaction(function () use ($intent) {
            $intent->increment('attempts');

            try {
                $response = $this->gateway->confirmIntent($intent);

                $intent->update([
                    'status' => PaymentStatus::SUCCEEDED,
                ]);

            } catch (Throwable $e) {
                $intent->update([
                    'status' => PaymentStatus::Failed,
                ]);

                throw $e;
            }

            return $intent;
        });
    }
}
