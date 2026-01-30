<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stuck Payment Pending Orders --}}
        <x-filament::section>
            <x-slot name="heading">
                Orders Stuck in Payment Pending
            </x-slot>
            <x-slot name="description">
                Orders awaiting payment for more than 30 minutes
            </x-slot>

            @if($this->getStuckPaymentPendingOrders()->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No anomalies detected.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left">Order</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Total</th>
                                <th class="px-4 py-2 text-left">Created</th>
                                <th class="px-4 py-2 text-left">Waiting</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getStuckPaymentPendingOrders() as $order)
                                <tr>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('filament.control-plane.resources.orders.view', ['record' => $order->id]) }}"
                                           class="text-primary-600 hover:underline">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">
                                        <x-filament::badge color="warning">
                                            {{ $order->status->value }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="px-4 py-2">${{ number_format($order->total_cents / 100, 2) }}</td>
                                    <td class="px-4 py-2">{{ $order->created_at->format('M d, H:i') }}</td>
                                    <td class="px-4 py-2 text-danger-600">{{ $order->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Stuck Approved Refunds --}}
        <x-filament::section>
            <x-slot name="heading">
                Approved Refunds Not Executed
            </x-slot>
            <x-slot name="description">
                Refunds approved more than 15 minutes ago but not yet processed
            </x-slot>

            @if($this->getStuckApprovedRefunds()->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No anomalies detected.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left">Refund ID</th>
                                <th class="px-4 py-2 text-left">Order</th>
                                <th class="px-4 py-2 text-left">Amount</th>
                                <th class="px-4 py-2 text-left">Approved At</th>
                                <th class="px-4 py-2 text-left">Waiting</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getStuckApprovedRefunds() as $refund)
                                <tr>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('filament.control-plane.resources.refunds.view', ['record' => $refund->id]) }}"
                                           class="text-primary-600 hover:underline">
                                            #{{ $refund->id }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('filament.control-plane.resources.orders.view', ['record' => $refund->order_id]) }}"
                                           class="text-primary-600 hover:underline">
                                            {{ $refund->order?->order_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">${{ number_format($refund->amount_cents / 100, 2) }}</td>
                                    <td class="px-4 py-2">{{ $refund->approved_at?->format('M d, H:i') }}</td>
                                    <td class="px-4 py-2 text-danger-600">{{ $refund->approved_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Payment Amount Mismatches --}}
        <x-filament::section>
            <x-slot name="heading">
                Payment Amount Mismatches
            </x-slot>
            <x-slot name="description">
                Payments where amount differs from order total
            </x-slot>

            @if($this->getPaymentAmountMismatches()->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No anomalies detected.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left">Payment ID</th>
                                <th class="px-4 py-2 text-left">Order</th>
                                <th class="px-4 py-2 text-left">Payment Amount</th>
                                <th class="px-4 py-2 text-left">Order Total</th>
                                <th class="px-4 py-2 text-left">Difference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getPaymentAmountMismatches() as $payment)
                                <tr>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('filament.control-plane.resources.payments.view', ['record' => $payment->id]) }}"
                                           class="text-primary-600 hover:underline">
                                            #{{ $payment->id }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('filament.control-plane.resources.orders.view', ['record' => $payment->order_id]) }}"
                                           class="text-primary-600 hover:underline">
                                            {{ $payment->order?->order_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">${{ number_format($payment->amount / 100, 2) }}</td>
                                    <td class="px-4 py-2">${{ number_format(($payment->order?->total_cents ?? 0) / 100, 2) }}</td>
                                    <td class="px-4 py-2 text-danger-600">
                                        ${{ number_format(abs($payment->amount - ($payment->order?->total_cents ?? 0)) / 100, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Stuck Processing Refunds --}}
        <x-filament::section>
            <x-slot name="heading">
                Stuck Processing Refunds
            </x-slot>
            <x-slot name="description">
                Refunds stuck in processing state for more than 5 minutes
            </x-slot>

            @if($this->getStuckProcessingRefunds()->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No anomalies detected.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left">Refund ID</th>
                                <th class="px-4 py-2 text-left">Order</th>
                                <th class="px-4 py-2 text-left">Amount</th>
                                <th class="px-4 py-2 text-left">Started Processing</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getStuckProcessingRefunds() as $refund)
                                <tr>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('filament.control-plane.resources.refunds.view', ['record' => $refund->id]) }}"
                                           class="text-primary-600 hover:underline">
                                            #{{ $refund->id }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('filament.control-plane.resources.orders.view', ['record' => $refund->order_id]) }}"
                                           class="text-primary-600 hover:underline">
                                            {{ $refund->order?->order_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">${{ number_format($refund->amount_cents / 100, 2) }}</td>
                                    <td class="px-4 py-2 text-danger-600">{{ $refund->updated_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Orders Without Payment Intent --}}
        <x-filament::section>
            <x-slot name="heading">
                Orders Without Payment Intent
            </x-slot>
            <x-slot name="description">
                Orders awaiting payment but missing a payment intent
            </x-slot>

            @if($this->getOrdersWithoutPaymentIntent()->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No anomalies detected.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left">Order</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Total</th>
                                <th class="px-4 py-2 text-left">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getOrdersWithoutPaymentIntent() as $order)
                                <tr>
                                    <td class="px-4 py-2">
                                        <a href="{{ route('filament.control-plane.resources.orders.view', ['record' => $order->id]) }}"
                                           class="text-primary-600 hover:underline">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">
                                        <x-filament::badge color="warning">
                                            {{ $order->status->value }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="px-4 py-2">${{ number_format($order->total_cents / 100, 2) }}</td>
                                    <td class="px-4 py-2">{{ $order->created_at->format('M d, H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Negative Stock Products --}}
        <x-filament::section>
            <x-slot name="heading">
                Negative Stock (Oversold)
            </x-slot>
            <x-slot name="description">
                Products where reserved quantity exceeds available quantity
            </x-slot>

            @if($this->getNegativeStockProducts()->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No anomalies detected.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left">SKU</th>
                                <th class="px-4 py-2 text-left">Product</th>
                                <th class="px-4 py-2 text-right">Available</th>
                                <th class="px-4 py-2 text-right">Reserved</th>
                                <th class="px-4 py-2 text-right">Deficit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getNegativeStockProducts() as $stock)
                                <tr>
                                    <td class="px-4 py-2 font-mono text-xs">
                                        <a href="{{ route('filament.control-plane.resources.inventory.view', ['record' => $stock->id]) }}"
                                           class="text-primary-600 hover:underline">
                                            {{ $stock->product?->sku }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">{{ $stock->product?->name }}</td>
                                    <td class="px-4 py-2 text-right">{{ $stock->quantity_available }}</td>
                                    <td class="px-4 py-2 text-right">{{ $stock->quantity_reserved }}</td>
                                    <td class="px-4 py-2 text-right text-danger-600">
                                        {{ $stock->quantity_reserved - $stock->quantity_available }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Stale Reservations --}}
        <x-filament::section>
            <x-slot name="heading">
                Stale Stock Reservations
            </x-slot>
            <x-slot name="description">
                Products with reservations that haven't been updated in 24+ hours
            </x-slot>

            @if($this->getStaleReservations()->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No anomalies detected.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left">SKU</th>
                                <th class="px-4 py-2 text-left">Product</th>
                                <th class="px-4 py-2 text-right">Reserved</th>
                                <th class="px-4 py-2 text-left">Last Updated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getStaleReservations() as $stock)
                                <tr>
                                    <td class="px-4 py-2 font-mono text-xs">
                                        <a href="{{ route('filament.control-plane.resources.inventory.view', ['record' => $stock->id]) }}"
                                           class="text-primary-600 hover:underline">
                                            {{ $stock->product?->sku }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2">{{ $stock->product?->name }}</td>
                                    <td class="px-4 py-2 text-right text-warning-600">{{ $stock->quantity_reserved }}</td>
                                    <td class="px-4 py-2">{{ $stock->updated_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
