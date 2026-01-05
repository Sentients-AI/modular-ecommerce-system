<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Idempotency\Models\IdempotencyKey;
use Illuminate\Database\Seeder;

final class IdempotencyKeySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IdempotencyKey::factory()->count(20)->create();
    }
}
