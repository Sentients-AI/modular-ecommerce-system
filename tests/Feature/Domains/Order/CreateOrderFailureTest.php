<?php

declare(strict_types=1);

use App\Domain\Cart\Exceptions\EmptyCartException;
use App\Domain\Cart\Models\Cart;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Order\Actions\CreateOrderFromCart;
use App\Domain\Order\DTOs\CreateOrderData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('order creation fails for empty cart', function () {
    $cart = Cart::factory()->create();

    $this->expectException(EmptyCartException::class);

    app(CreateOrderFromCart::class)->execute(
        new CreateOrderData(
            userId: (int) $cart->user_id,
            cartId: (string) $cart->id,
            currency: 'USD'
        )
    );
});

it('order creation fails when stock is insufficient', function () {
    $stock = Stock::factory()->create([
        'quantity_available' => 1,
        'quantity_reserved' => 0,
    ]);

    $cart = Cart::factory()
        ->withProduct($stock->product_id, quantity: 10)
        ->create();

    $this->expectException(InsufficientStockException::class);

    app(CreateOrderFromCart::class)->execute(
        new CreateOrderData(
            userId: $cart->user_id,
            cartId: (string) $cart->id,
            currency: 'USD'
        )
    );
});
