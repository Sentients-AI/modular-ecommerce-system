<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Idempotency\Models\IdempotencyKey;
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
            'user_id' => \App\Domain\User\Models\User::factory(),
            'response_hash' => hash('sha256', $this->faker->text()),
        ];
    }
}
