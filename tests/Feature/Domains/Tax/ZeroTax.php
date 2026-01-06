<?php

declare(strict_types=1);

namespace Tax;

use App\Domain\Cart\Models\Cart;
use App\Domain\Order\Actions\CreateOrderFromCart;
use App\Domain\Order\DTOs\CreateOrderData;

it('test tax is zero when tax is disabled', function () {

    config(['tax.enabled' => false]);

    $cart = Cart::factory()->withItems()->create();

    $order = app(CreateOrderFromCart::class)->execute(
        new CreateOrderData(
            userId: $cart->user_id,
            cartId: $cart->id,
            currency: 'USD'
        )
    );

    expect($order->tax_cents)->toBe(0);
});
