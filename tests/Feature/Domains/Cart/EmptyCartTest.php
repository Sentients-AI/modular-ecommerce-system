<?php

declare(strict_types=1);

use App\Domain\Cart\Exceptions\EmptyCartException;
use App\Domain\Cart\Models\Cart;
use App\Domain\Order\Actions\CheckoutAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('checkout fails when cart is empty', function () {
    $cart = Cart::factory()->create();

    $this->expectException(EmptyCartException::class);

    $action = app(CheckoutAction::class);
    $action->execute($cart);
});
