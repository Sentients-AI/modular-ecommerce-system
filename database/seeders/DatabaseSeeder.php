<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            StockSeeder::class,
            StockMovementSeeder::class,
            CartSeeder::class,
            OrderSeeder::class,
            PaymentSeeder::class,
            IdempotencyKeySeeder::class,
        ]);

    }
}
