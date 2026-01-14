<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Cart>
 */
final class CartFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Cart::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
        ];
    }

    public function withProduct(int $productId, int $quantity = 1): self
    {
        return $this->has(
            CartItem::factory()->state([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]),
            'items'
        );
    }

    public function withItems(int $count = 1): self
    {
        return $this->has(
            CartItem::factory()->count($count),
            'items'
        );
    }
}
