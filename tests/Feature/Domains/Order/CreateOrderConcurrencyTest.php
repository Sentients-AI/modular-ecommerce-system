<?php

declare(strict_types=1);

use App\Domain\Cart\Models\Cart;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Order\Actions\CreateOrderFromCart;
use App\Domain\Order\DTOs\CreateOrderData;
use App\Domain\Order\Models\Order;
use App\Domain\User\Models\User;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it(/**
 * @throws Exception
 */ 'test only one order can reserve limited stock', function () {

    // Arrange
    $stock = Stock::factory()->create([
        'quantity_available' => 1,
        'quantity_reserved' => 0,
    ]);

    $cartA = Cart::factory()->withProduct($stock->product_id, 1)->create();
    $cartB = Cart::factory()->withProduct($stock->product_id, 1)->create();

    $action = app(CreateOrderFromCart::class);

    // Act
    $orderA = $action->execute(new CreateOrderData(
        userId: (int) $cartA->user_id,
        cartId: (string) $cartA->id,
        currency: 'USD'
    ));

    try {
        $action->execute(new CreateOrderData(
            userId: $cartB->user_id,
            cartId: $cartB->id,
            currency: 'USD'
        ));
        $this->fail('Second order should not succeed');
    } catch (Throwable) {
        // expected
    }

    // Assert
    $this->assertEquals(0, $stock->fresh()->quantity_available);
    $this->assertDatabaseCount('orders', 1);
});

it('prevents duplicate order creation under concurrency', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->for($user)->withItems(3)->create();

    $payload = [
        'cart_id' => $cart->id,
        'currency' => 'USD',
    ];

    $key = 'checkout-concurrent-test';

    $responses = collect();

    parallel([
        fn () => $responses->push(
            $this->postJson('/api/checkout', $payload, [
                'Idempotency-Key' => $key,
            ])
        ),
        fn () => $responses->push(
            $this->postJson('/api/checkout', $payload, [
                'Idempotency-Key' => $key,
            ])
        ),
    ]);

    expect(Order::query()->count())->toBe(1);

    $responses->each(fn ($response) => $response->assertStatus(200)
    );
})->skip('Requires amphp/parallel package for concurrent testing');

it('validates stock before creating order', function () {
    $stock = Stock::factory()->create([
        'quantity_available' => 0,
        'quantity_reserved' => 0,
    ]);

    $cart = Cart::factory()->withProduct($stock->product_id, 1)->create();

    $action = app(CreateOrderFromCart::class);

    $action->execute(new CreateOrderData(
        userId: (int) $cart->user_id,
        cartId: (string) $cart->id,
        currency: 'USD'
    ));
})->throws(InsufficientStockException::class);

it('does not create order when stock validation fails', function () {
    $stock = Stock::factory()->create([
        'quantity_available' => 0,
        'quantity_reserved' => 0,
    ]);

    $cart = Cart::factory()->withProduct($stock->product_id, 1)->create();

    $action = app(CreateOrderFromCart::class);

    try {
        $action->execute(new CreateOrderData(
            userId: (int) $cart->user_id,
            cartId: (string) $cart->id,
            currency: 'USD'
        ));
    } catch (InsufficientStockException) {
        // expected
    }

    expect(Order::query()->count())->toBe(0);
});

it('throws exception when creating order from completed cart', function () {
    $stock = Stock::factory()->create([
        'quantity_available' => 10,
        'quantity_reserved' => 0,
    ]);

    $cart = Cart::factory()->withProduct($stock->product_id, 1)->create([
        'completed_at' => now(),
    ]);

    $action = app(CreateOrderFromCart::class);

    $action->execute(new CreateOrderData(
        userId: (int) $cart->user_id,
        cartId: (string) $cart->id,
        currency: 'USD'
    ));
})->throws(DomainException::class, 'Cannot modify a completed cart');

it('marks cart as completed after successful order creation', function () {
    $stock = Stock::factory()->create([
        'quantity_available' => 10,
        'quantity_reserved' => 0,
    ]);

    $cart = Cart::factory()->withProduct($stock->product_id, 1)->create();

    expect($cart->isCompleted())->toBeFalse();

    $action = app(CreateOrderFromCart::class);
    $action->execute(new CreateOrderData(
        userId: (int) $cart->user_id,
        cartId: (string) $cart->id,
        currency: 'USD'
    ));

    $cart->refresh();
    expect($cart->isCompleted())->toBeTrue();
    expect($cart->items()->count())->toBe(0);
});
