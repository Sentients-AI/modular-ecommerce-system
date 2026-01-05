<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Product\Models\Product;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
final class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory()->create(),
            'stock_id' => Stock::factory(),
            'type' => $this->faker->randomElement(StockMovementType::getTypes()),
            'quantity' => $this->faker->numberBetween(1, 100),
            'reference_type' => $this->faker->optional()->randomElement(['App\\Domain\\Order\\Models\\Order', 'App\\Domain\\Cart\\Models\\Cart']),
            'reference_id' => $this->faker->optional()->numberBetween(1, 100),
            'reason' => $this->faker->text,
        ];
    }
}
