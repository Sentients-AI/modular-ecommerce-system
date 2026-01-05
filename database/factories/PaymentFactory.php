<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
final class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider' => $this->faker->randomElement(['stripe', 'paypal', 'square']),
            'provider_reference' => $this->faker->unique()->bothify('pay_??????????'),
            'status' => $this->faker->randomElement(['pending', 'succeeded', 'failed']),
            'amount_cents' => $this->faker->numberBetween(1000, 100000),
            'currency' => 'USD',
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'succeeded',
        ]);
    }

    public function failed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
