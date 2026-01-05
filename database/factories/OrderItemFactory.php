<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Domain\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
final class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'price_cents_snapshot' => $this->faker->numberBetween(1000, 50000),
            'tax_cents_snapshot' => $this->faker->numberBetween(0, 5000),
            'quantity' => $this->faker->numberBetween(1, 5),
        ];
    }
}
