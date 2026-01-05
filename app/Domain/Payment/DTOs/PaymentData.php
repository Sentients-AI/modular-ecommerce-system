<?php

declare(strict_types=1);

namespace App\Domain\Payment\DTOs;

use App\Shared\DTOs\BaseData;

final class PaymentData extends BaseData
{
    public function __construct(
        public string $orderId,
        public string $paymentMethod,
        public string $paymentGateway,
        public string $amount,
        public string $currency = 'USD',
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            orderId: $data['order_id'],
            paymentMethod: $data['payment_method'],
            paymentGateway: $data['payment_gateway'],
            amount: $data['amount'],
            currency: $data['currency'] ?? 'USD',
        );
    }
}
