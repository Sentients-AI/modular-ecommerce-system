<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment\Stripe;

use App\Domain\Payment\Contracts\PaymentGatewayService as PaymentGatewayServiceContract;
use App\Domain\Payment\DTOs\ProviderResponse;
use App\Domain\Payment\Models\PaymentIntent;
use Stripe\StripeClient;

final class PaymentGatewayService implements PaymentGatewayServiceContract
{
    public function __construct(
        private StripeClient $stripe
    ) {}

    public function createIntent(PaymentIntent $intent): ProviderResponse
    {
        $pi = $this->stripe->paymentIntents->create([
            'amount' => $intent->amount,
            'currency' => $intent->currency,
            'metadata' => [
                'payment_intent_id' => $intent->id,
                'order_id' => $intent->order_id,
            ],
        ]);

        return new ProviderResponse(
            provider: 'stripe',
            reference: $pi->id,
            clientSecret: $pi->client_secret,
        );
    }

    public function confirmIntent(PaymentIntent $intent): ProviderResponse
    {
        $pi = $this->stripe->paymentIntents->confirm(
            $intent->provider_reference
        );

        return new ProviderResponse(
            provider: 'stripe',
            reference: $pi->id,
            clientSecret: $pi->client_secret,
        );
    }

    public function cancelIntent(PaymentIntent $intent): void
    {
        $this->stripe->paymentIntents->cancel(
            $intent->provider_reference
        );
    }

    public function refund(string $paymentIntentId, int $amountCents): void
    {
        $this->stripe->refunds->create([
            'payment_intent' => $paymentIntentId,
            'amount' => $amountCents,
        ]);
    }
}
