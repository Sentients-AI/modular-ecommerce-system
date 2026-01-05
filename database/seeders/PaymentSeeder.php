<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Payment\Models\Payment;
use Illuminate\Database\Seeder;

final class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Payment::factory()->count(30)->create();
        Payment::factory()->count(10)->completed()->create();
        Payment::factory()->count(5)->failed()->create();
    }
}
