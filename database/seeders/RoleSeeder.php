<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Role\Models\Role;
use Illuminate\Database\Seeder;

final class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator']
        );
        Role::firstOrCreate(
            ['name' => 'customer'],
            ['description' => 'Customer']
        );
        Role::firstOrCreate(
            ['name' => 'manager'],
            ['description' => 'Manager']
        );
    }
}
