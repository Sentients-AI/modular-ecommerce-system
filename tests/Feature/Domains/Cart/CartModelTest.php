<?php

declare(strict_types=1);

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Product\Models\Product;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('belongs to a user', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->create(['user_id' => $user->id]);

    expect($cart->user)->toBeInstanceOf(User::class);
    expect($cart->user->id)->toBe($user->id);
});

it('has many cart items', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create();

    $cart->items()->create([
        'product_id' => $product->id,
        'price_cents_snapshot' => 1000,
        'tax_cents_snapshot' => 100,
        'quantity' => 2,
    ]);

    expect($cart->items)->toHaveCount(1);
    expect($cart->items->first())->toBeInstanceOf(CartItem::class);
});

it('calculates total correctly', function () {
    $cart = Cart::factory()->create();
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    $cart->items()->create([
        'product_id' => $product1->id,
        'price_cents_snapshot' => 1000,
        'tax_cents_snapshot' => 100,
        'quantity' => 2,
    ]);

    $cart->items()->create([
        'product_id' => $product2->id,
        'price_cents_snapshot' => 500,
        'tax_cents_snapshot' => 50,
        'quantity' => 3,
    ]);

    $items = $cart->items;
    $subtotal = $items->sum(fn ($item) => $item->price_cents_snapshot * $item->quantity);
    $tax = $items->sum(fn ($item) => $item->tax_cents_snapshot * $item->quantity);

    expect($subtotal)->toBe(3500);
    expect($tax)->toBe(350);
});

it('can have session id for guest carts', function () {
    $cart = Cart::factory()->create([
        'user_id' => null,
        'session_id' => 'guest_session_123',
    ]);

    expect($cart->user_id)->toBeNull();
    expect($cart->session_id)->toBe('guest_session_123');
});

it('clears items when cart is emptied', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create();

    $cart->items()->create([
        'product_id' => $product->id,
        'price_cents_snapshot' => 1000,
        'tax_cents_snapshot' => 100,
        'quantity' => 2,
    ]);

    expect($cart->items()->count())->toBe(1);

    $cart->items()->delete();

    expect($cart->items()->count())->toBe(0);
});
