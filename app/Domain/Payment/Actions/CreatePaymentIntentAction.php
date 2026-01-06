<?php

namespace App\Domain\Payment\Actions;
use App\Domain\Payment\Contracts\PaymentGatewayService;
use App\Domain\Payment\DTOs\CreatePaymentIntentDTO;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntent;
use Illuminate\Support\Facades\DB;

final class CreatePaymentIntentAction
{
    public function __construct(
        private PaymentGatewayService $gateway
    ) {}

    public function execute(CreatePaymentIntentDTO $dto): PaymentIntent
    {
        return DB::transaction(function () use ($dto) {

            // Idempotency guard
            $existing = PaymentIntent::query()->where('idempotency_key', $dto->idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            // Persist intent FIRST
            $intent = PaymentIntent::query()->create([
                'order_id' => $dto->orderId,
                'amount'   => $dto->amount,
                'currency' => $dto->currency,
                'status'   => PaymentStatus::REQUIRES_PAYMENT,
                'idempotency_key' => $dto->idempotencyKey,
                'metadata' => $dto->metadata,
            ]);

            // Call provider (side effect)
            $response = $this->gateway->createIntent($intent);

            // Persist provider reference
            $intent->update([
                'provider'            => $response->provider(),
                'provider_reference'  => $response->reference(),
                'status'              => PaymentStatus::PROCESSING,
            ]);

            return $intent;
        });
    }
}
