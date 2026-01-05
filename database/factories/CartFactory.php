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
            'status' => $this->faker->randomElement(['active', 'converted']),
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function converted(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'converted',
        ]);
    }

    public function withItems(int $count = 1): self
    {
        return $this->has(
            CartItem::factory()->count($count),
            'items'
        );
    }
}
