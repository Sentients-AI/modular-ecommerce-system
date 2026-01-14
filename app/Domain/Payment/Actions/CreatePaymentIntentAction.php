<?php

declare(strict_types=1);

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

            // Call provider (side effect) first to get provider info
            // Create a temporary intent object for the gateway call
            $tempIntent = new PaymentIntent([
                'order_id' => $dto->orderId,
                'amount' => $dto->amount,
                'currency' => $dto->currency,
                'metadata' => $dto->metadata,
            ]);

            $response = $this->gateway->createIntent($tempIntent);

            // Persist intent with provider info
            $intent = PaymentIntent::query()->create([
                'order_id' => $dto->orderId,
                'amount' => $dto->amount,
                'currency' => $dto->currency,
                'provider' => $response->provider(),
                'provider_reference' => $response->reference(),
                'status' => PaymentStatus::Processing,
                'idempotency_key' => $dto->idempotencyKey,
                'metadata' => $dto->metadata,
            ]);

            return $intent;
        });
    }
}
