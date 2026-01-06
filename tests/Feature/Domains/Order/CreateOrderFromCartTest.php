<?php

declare(strict_types=1);

namespace tests\Feature\Domains\Order\CreateOrderFromCart;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Order\Actions\CreateOrderFromCart;
use App\Domain\Order\DTOs\CreateOrderData;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Product\Models\Product;
use Database\Factories\CartFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('order is created from cart and stock is reserved', function () {
    // Arrange
    $cart = CartFactory::new()->create();

    $cartItem = $cart->items()->create([
        'product_id' => Product::factory()->create()->id,
        'price_cents_snapshot' => 5000,
        'tax_cents_snapshot' => 500,
        'quantity' => 6,
    ]);

    $stock = Stock::factory()->create([
        'product_id' => $cartItem->product_id,
        'quantity_available' => 10,
        'quantity_reserved' => 3,
    ]);

    // Act
    $order = app(CreateOrderFromCart::class)->execute(
        new CreateOrderData(
            userId: $cart->user_id,
            cartId: (string) $cart->id,
            currency: 'USD'
        )
    );

    // Assert
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
    ]);

    $this->assertDatabaseMissing('cart_items', [
        'cart_id' => $cart->id,
    ]);

    $this->assertEquals(9, $stock->fresh()->quantity_reserved);
});

it('order status transitions', function () {
    $this->assertTrue(
        OrderStatus::Pending->canTransitionTo(OrderStatus::Paid)
    );

    $this->assertFalse(
        OrderStatus::Shipped->canTransitionTo(OrderStatus::Paid)
    );
});
