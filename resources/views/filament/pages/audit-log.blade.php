<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Audit Log
            </x-slot>
            <x-slot name="description">
                Immutable record of all actions performed in the system. This log cannot be edited or deleted.
            </x-slot>

            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
