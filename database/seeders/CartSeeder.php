<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Models\CartItem;
use Illuminate\Database\Seeder;

final class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cart::factory()
            ->count(20)
            ->has(CartItem::factory()->count(3), 'items')
            ->create();
    }
}
