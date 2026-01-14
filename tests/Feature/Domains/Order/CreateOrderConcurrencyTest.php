<?php

declare(strict_types=1);

use App\Domain\Cart\Models\Cart;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Order\Actions\CreateOrderFromCart;
use App\Domain\Order\DTOs\CreateOrderData;
use App\Domain\Order\Models\Order;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Throwable;

uses(TestCase::class, RefreshDatabase::class);

it('test only one order can reserve limited stock', function () {

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
