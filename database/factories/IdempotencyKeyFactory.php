<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Idempotency\Models\IdempotencyKey;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdempotencyKey>
 */
final class IdempotencyKeyFactory extends Factory
{
    protected $model = IdempotencyKey::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => $this->faker->unique()->uuid(),
            'user_id' => User::factory(),
            'response_body' => hash('sha256', $this->faker->text()),
            'response_code' => $this->faker->randomElement([200, 201]),
            'created_at' => $this->faker->dateTime(),
            'expires_at' => $this->faker->dateTime('+1 month'),
            'request_fingerprint' => $this->faker->sha256(),
            'action' => $this->faker->word(),
        ];
    }
}
