<?php

declare(strict_types=1);

namespace App\Application\UseCases\Order;

use App\Application\DTOs\Request\CheckoutRequest;
use App\Application\DTOs\Response\CheckoutResponse;
use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Specifications\CartHasItems;
use App\Domain\Cart\Specifications\CartIsNotCompleted;
use App\Domain\Order\Actions\CreateOrderFromCart;
use App\Domain\Order\DTOs\CreateOrderData;
use App\Domain\Payment\Actions\CreatePaymentIntentAction;
use App\Domain\Payment\DTOs\CreatePaymentIntentDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CheckoutUseCase
{
    public function __construct(
        private CreateOrderFromCart $createOrderFromCart,
        private CreatePaymentIntentAction $createPaymentIntent,
    ) {}

    public function execute(CheckoutRequest $request): CheckoutResponse
    {
        return DB::transaction(function () use ($request) {
            // Load cart with items
            $cart = Cart::query()
                ->with('items.product')
                ->findOrFail($request->cartId->toInt());

            // Validate using composed specifications
            $cartSpec = (new CartIsNotCompleted())->and(new CartHasItems());
            $cartSpec->assertSatisfiedBy($cart);

            // Create order through domain action
            $order = $this->createOrderFromCart->execute(
                new CreateOrderData(
                    userId: $request->userId->toInt(),
                    cartId: (string) $request->cartId->toInt(),
                    currency: $request->currency,
                )
            );

            // Create payment intent
            $idempotencyKey = $request->idempotencyKey ?? Str::uuid()->toString();
            $paymentIntent = $this->createPaymentIntent->execute(
                new CreatePaymentIntentDTO(
                    orderId: $order->id,
                    amount: $order->total_cents,
                    currency: $order->currency,
                    idempotencyKey: $idempotencyKey,
                )
            );

            return CheckoutResponse::fromOrder($order, $paymentIntent);
        });
    }
}
