<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\FeatureFlag\Models\FeatureFlag;
use Illuminate\Database\Seeder;

final class FeatureFlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $flags = [
            [
                'key' => 'new_checkout_flow',
                'name' => 'New Checkout Flow',
                'description' => 'Enable the redesigned checkout experience',
                'is_enabled' => false,
            ],
            [
                'key' => 'guest_checkout',
                'name' => 'Guest Checkout',
                'description' => 'Allow users to checkout without creating an account',
                'is_enabled' => true,
            ],
            [
                'key' => 'product_reviews',
                'name' => 'Product Reviews',
                'description' => 'Enable customer reviews on product pages',
                'is_enabled' => false,
            ],
            [
                'key' => 'wishlist',
                'name' => 'Wishlist Feature',
                'description' => 'Allow customers to save products to a wishlist',
                'is_enabled' => false,
            ],
            [
                'key' => 'express_shipping',
                'name' => 'Express Shipping',
                'description' => 'Offer express shipping option at checkout',
                'is_enabled' => true,
            ],
            [
                'key' => 'loyalty_points',
                'name' => 'Loyalty Points Program',
                'description' => 'Enable loyalty points earning and redemption',
                'is_enabled' => false,
            ],
        ];

        foreach ($flags as $flag) {
            FeatureFlag::query()->updateOrCreate(
                ['key' => $flag['key']],
                $flag
            );
        }
    }
}
