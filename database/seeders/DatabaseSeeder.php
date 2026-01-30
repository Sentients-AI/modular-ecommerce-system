<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\User\Models\User;
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
        User::factory(1)->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        //        $this->call([
        //            RoleSeeder::class,
        //            UserSeeder::class,
        //            CategorySeeder::class,
        //            ProductSeeder::class,
        //            StockSeeder::class,
        //            StockMovementSeeder::class,
        //            CartSeeder::class,
        //            OrderSeeder::class,
        //            PaymentIntentSeeder::class,
        //            IdempotencyKeySeeder::class,
        //        ]);

    }
}
