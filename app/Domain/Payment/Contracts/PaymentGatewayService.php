<?php

namespace App\Domain\Payment\Contracts;

use App\Domain\Payment\Models\PaymentIntent;
use App\Domain\Payment\DTOs\CreatePaymentIntentDTO;

interface PaymentGatewayService
{
    public function createIntent(PaymentIntent $intent): CreatePaymentIntentDTO;

    public function confirmIntent(PaymentIntent $intent): CreatePaymentIntentDTO;

    public function cancelIntent(PaymentIntent $intent): void;
}
