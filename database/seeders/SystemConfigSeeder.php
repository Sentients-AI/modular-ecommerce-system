<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Config\Models\SystemConfig;
use Illuminate\Database\Seeder;

final class SystemConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            // Tax configuration
            [
                'group' => 'tax',
                'key' => 'enabled',
                'name' => 'Tax Enabled',
                'description' => 'Enable or disable tax calculation',
                'type' => 'boolean',
                'value' => 'true',
                'default_value' => 'true',
                'is_sensitive' => false,
            ],
            [
                'group' => 'tax',
                'key' => 'rate',
                'name' => 'Tax Rate',
                'description' => 'Default tax rate (decimal, e.g., 0.10 for 10%)',
                'type' => 'float',
                'value' => '0.10',
                'default_value' => '0.10',
                'validation_rules' => ['min:0', 'max:1'],
                'is_sensitive' => false,
            ],
            [
                'group' => 'tax',
                'key' => 'calculation_mode',
                'name' => 'Tax Calculation Mode',
                'description' => 'How tax is calculated: inclusive or exclusive',
                'type' => 'string',
                'value' => 'exclusive',
                'default_value' => 'exclusive',
                'is_sensitive' => false,
            ],

            // Shipping configuration
            [
                'group' => 'shipping',
                'key' => 'free_shipping_threshold',
                'name' => 'Free Shipping Threshold',
                'description' => 'Order amount (in cents) for free shipping',
                'type' => 'integer',
                'value' => '10000',
                'default_value' => '10000',
                'is_sensitive' => false,
            ],
            [
                'group' => 'shipping',
                'key' => 'default_rate',
                'name' => 'Default Shipping Rate',
                'description' => 'Default shipping cost in cents',
                'type' => 'integer',
                'value' => '500',
                'default_value' => '500',
                'is_sensitive' => false,
            ],

            // Payment configuration
            [
                'group' => 'payment',
                'key' => 'stripe_enabled',
                'name' => 'Stripe Enabled',
                'description' => 'Enable Stripe payment gateway',
                'type' => 'boolean',
                'value' => 'true',
                'default_value' => 'true',
                'is_sensitive' => false,
            ],
            [
                'group' => 'payment',
                'key' => 'stripe_secret_key',
                'name' => 'Stripe Secret Key',
                'description' => 'Stripe API secret key',
                'type' => 'string',
                'value' => null,
                'default_value' => null,
                'is_sensitive' => true,
            ],
            [
                'group' => 'payment',
                'key' => 'payment_timeout_minutes',
                'name' => 'Payment Timeout',
                'description' => 'Minutes before a pending payment times out',
                'type' => 'integer',
                'value' => '30',
                'default_value' => '30',
                'is_sensitive' => false,
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::query()->updateOrCreate(
                ['group' => $config['group'], 'key' => $config['key']],
                $config
            );
        }
    }
}
