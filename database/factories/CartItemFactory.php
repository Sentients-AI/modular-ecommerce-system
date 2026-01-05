<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<CartItem>
 */
final class CartItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = CartItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'price_cents_snapshot' => $this->faker->numberBetween(1000, 50000),
            'tax_cents_snapshot' => $this->faker->numberBetween(0, 5000),
            'quantity' => $this->faker->numberBetween(1, 5),
        ];
    }
}
