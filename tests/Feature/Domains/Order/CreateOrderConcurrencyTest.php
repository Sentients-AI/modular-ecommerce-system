<?php

declare(strict_types=1);

namespace tests\Feature\Domains\Order\CreateOrderFromCart;

use App\Domain\Cart\Models\Cart;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Order\Actions\CreateOrderFromCart;
use App\Domain\Order\DTOs\CreateOrderData;
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
