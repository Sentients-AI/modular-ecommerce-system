<?php

declare(strict_types=1);

use App\Domain\Cart\Actions\AddItemToCart;
use App\Domain\Cart\Actions\GetCart;
use App\Domain\Cart\Actions\RemoveItemFromCart;
use App\Domain\Cart\DTOs\CartItemData;
use App\Domain\Cart\Models\Cart;
use App\Domain\Product\Models\Product;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('GetCart', function () {
    it('returns existing cart for user', function () {
        $user = User::factory()->create();
        $existingCart = Cart::factory()->create(['user_id' => $user->id]);

        $action = app(GetCart::class);
        $cart = $action->execute((string) $user->id, null);

        expect($cart->id)->toBe($existingCart->id);
    });

    it('creates new cart for user without cart', function () {
        $user = User::factory()->create();

        $action = app(GetCart::class);
        $cart = $action->execute((string) $user->id, null);

        expect($cart)->toBeInstanceOf(Cart::class);
        expect((int) $cart->user_id)->toBe($user->id);
    });

    it('returns cart for session', function () {
        $sessionId = 'guest_session_456';
        $existingCart = Cart::factory()->create([
            'user_id' => null,
            'session_id' => $sessionId,
        ]);

        $action = app(GetCart::class);
        $cart = $action->execute(null, $sessionId);

        expect($cart->id)->toBe($existingCart->id);
    });

    it('throws exception when no user or session provided', function () {
        $action = app(GetCart::class);
        $action->execute(null, null);
    })->throws(InvalidArgumentException::class, 'Either userId or sessionId must be provided');
});

describe('AddItemToCart', function () {
    it('adds item to cart', function () {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create([
            'price_cents' => 2500,
        ]);

        $action = app(AddItemToCart::class);
        $action->execute($cart, new CartItemData(
            productId: (string) $product->id,
            quantity: 2,
        ));

        expect($cart->items()->count())->toBe(1);
        expect($cart->items()->first()->product_id)->toBe($product->id);
        expect($cart->items()->first()->quantity)->toBe(2);
    });

    it('updates quantity for existing item', function () {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create([
            'price_cents' => 2500,
        ]);

        $cart->items()->create([
            'product_id' => $product->id,
            'price_cents_snapshot' => 2500,
            'tax_cents_snapshot' => 250,
            'quantity' => 1,
        ]);

        $action = app(AddItemToCart::class);
        $action->execute($cart, new CartItemData(
            productId: (string) $product->id,
            quantity: 3,
        ));

        expect($cart->items()->count())->toBe(1);
        expect($cart->items()->first()->quantity)->toBe(4);
    });
});

describe('RemoveItemFromCart', function () {
    it('removes item from cart', function () {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();

        $cart->items()->create([
            'product_id' => $product->id,
            'price_cents_snapshot' => 2500,
            'tax_cents_snapshot' => 250,
            'quantity' => 2,
        ]);

        $action = app(RemoveItemFromCart::class);
        $result = $action->execute($cart, (string) $product->id);

        expect($result)->toBeTrue();
        expect($cart->items()->count())->toBe(0);
    });

    it('returns false when item not found', function () {
        $cart = Cart::factory()->create();

        $action = app(RemoveItemFromCart::class);
        $result = $action->execute($cart, '999');

        expect($result)->toBeFalse();
    });
});
