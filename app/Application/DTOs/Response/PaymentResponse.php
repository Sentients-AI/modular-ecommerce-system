<?php

declare(strict_types=1);

namespace App\Application\DTOs\Response;

use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Payment\Models\PaymentIntent;
use App\Domain\Payment\ValueObjects\PaymentIntentId;

final readonly class PaymentResponse
{
    public function __construct(
        public PaymentIntentId $paymentIntentId,
        public OrderId $orderId,
        public int $amount,
        public string $currency,
        public string $status,
        public ?string $providerReference = null,
    ) {}

    public static function fromPaymentIntent(PaymentIntent $paymentIntent): self
    {
        return new self(
            paymentIntentId: PaymentIntentId::fromInt($paymentIntent->id),
            orderId: OrderId::fromInt($paymentIntent->order_id),
            amount: $paymentIntent->amount,
            currency: $paymentIntent->currency,
            status: $paymentIntent->status->value,
            providerReference: $paymentIntent->provider_reference,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'payment_intent_id' => $this->paymentIntentId->toInt(),
            'order_id' => $this->orderId->toInt(),
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'provider_reference' => $this->providerReference,
        ];
    }
}
