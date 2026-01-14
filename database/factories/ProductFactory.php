<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Category\Models\Category;
use App\Domain\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Model>
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => $this->faker->unique()->bothify('SKU-####-????'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->paragraph(),
            'price_cents' => $this->faker->numberBetween(1000, 100000),
            'currency' => 'MYR',
            'is_active' => $this->faker->boolean(90),
            'slug' => $this->faker->slug(),
            'images' => $this->faker->imageUrl(),
            'meta_title' => $this->faker->optional()->sentence(6),
            'meta_description' => json_encode([
                'content' => $this->faker->optional()->paragraph(),
            ]),
            'sale_price' => $this->faker->numberBetween(1000, 100000),
            'category_id' => Category::factory()->create()->id,
        ];
    }
}
