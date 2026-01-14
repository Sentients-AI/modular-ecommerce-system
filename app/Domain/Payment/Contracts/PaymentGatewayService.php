<?php

declare(strict_types=1);

namespace App\Domain\Payment\Contracts;

use App\Domain\Payment\DTOs\ProviderResponse;
use App\Domain\Payment\Models\PaymentIntent;

interface PaymentGatewayService
{
    public function createIntent(PaymentIntent $intent): ProviderResponse;

    public function confirmIntent(PaymentIntent $intent): ProviderResponse;

    public function cancelIntent(PaymentIntent $intent): void;

    public function refund(string $paymentIntentId, int $amountCents): void;
}
