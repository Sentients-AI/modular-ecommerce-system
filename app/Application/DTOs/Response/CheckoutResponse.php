<?php

declare(strict_types=1);

namespace App\Application\DTOs\Response;

use App\Domain\Order\Models\Order;
use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\Payment\Models\PaymentIntent;
use App\Domain\Payment\ValueObjects\PaymentIntentId;

final readonly class CheckoutResponse
{
    public function __construct(
        public OrderId $orderId,
        public string $orderNumber,
        public int $totalCents,
        public string $currency,
        public string $status,
        public ?PaymentIntentId $paymentIntentId = null,
        public ?string $providerReference = null,
    ) {}

    public static function fromOrder(Order $order, ?PaymentIntent $paymentIntent = null): self
    {
        return new self(
            orderId: OrderId::fromInt($order->id),
            orderNumber: $order->order_number,
            totalCents: $order->total_cents,
            currency: $order->currency,
            status: $order->status->value,
            paymentIntentId: $paymentIntent ? PaymentIntentId::fromInt($paymentIntent->id) : null,
            providerReference: $paymentIntent?->provider_reference,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId->toInt(),
            'order_number' => $this->orderNumber,
            'total_cents' => $this->totalCents,
            'currency' => $this->currency,
            'status' => $this->status,
            'payment_intent_id' => $this->paymentIntentId?->toInt(),
            'provider_reference' => $this->providerReference,
        ];
    }
}
