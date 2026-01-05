<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Inventory\Models\Stock;
use Illuminate\Database\Seeder;

final class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Stock::factory()->count(50)->create();
    }
}
