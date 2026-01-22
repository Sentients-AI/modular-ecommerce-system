<?php

declare(strict_types=1);

namespace App\Application\UseCases\Payment;

use App\Application\DTOs\Request\ProcessPaymentRequest;
use App\Application\DTOs\Response\PaymentResponse;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Specifications\OrderCanTransitionToStatus;
use App\Domain\Payment\Actions\ConfirmPaymentIntentAction;
use App\Domain\Payment\Models\PaymentIntent;
use App\Domain\Payment\Specifications\PaymentCanBeConfirmed;

final readonly class ProcessPaymentUseCase
{
    public function __construct(
        private ConfirmPaymentIntentAction $confirmPaymentIntent,
    ) {}

    public function execute(ProcessPaymentRequest $request): PaymentResponse
    {
        $paymentIntent = PaymentIntent::query()
            ->with('order')
            ->findOrFail($request->paymentIntentId->toInt());

        // Validate payment can be confirmed
        (new PaymentCanBeConfirmed())->assertSatisfiedBy($paymentIntent);

        // Validate order can transition to paid
        (new OrderCanTransitionToStatus(OrderStatus::Paid))
            ->assertSatisfiedBy($paymentIntent->order);

        // Execute confirmation
        $confirmedIntent = $this->confirmPaymentIntent->execute($paymentIntent);

        return PaymentResponse::fromPaymentIntent($confirmedIntent);
    }
}
