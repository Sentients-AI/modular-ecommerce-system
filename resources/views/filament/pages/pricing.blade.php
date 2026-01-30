<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Product Pricing
            </x-slot>
            <x-slot name="description">
                Manage product prices with full audit trails. Changes can be made immediately or scheduled for future dates.
            </x-slot>

            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
