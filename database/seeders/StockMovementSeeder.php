<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Inventory\Models\StockMovement;
use Illuminate\Database\Seeder;

final class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StockMovement::factory()->count(100)->create();
    }
}
