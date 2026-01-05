<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Cart\Models;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 5),
            'price' => $this->faker->numberBetween(100, 10000),
        ];
    }
}
