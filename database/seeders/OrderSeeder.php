<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use Illuminate\Database\Seeder;

final class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory()
            ->count(30)
            ->has(OrderItem::factory()->count(3), 'items')
            ->create();

        Order::factory()
            ->count(10)
            ->completed()
            ->has(OrderItem::factory()->count(2), 'items')
            ->create();

        Order::factory()
            ->count(5)
            ->cancelled()
            ->has(OrderItem::factory()->count(2), 'items')
            ->create();
    }
}
