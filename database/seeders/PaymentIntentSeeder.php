<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Payment\Models\PaymentIntent;
use Illuminate\Database\Seeder;

final class PaymentIntentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentIntent::factory()
            ->count(10)
            ->create();
    }
}
