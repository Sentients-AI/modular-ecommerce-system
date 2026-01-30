<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Shared\Domain\DomainEventRecord;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('Order Number'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (OrderStatus $state): string => match ($state) {
                                OrderStatus::Pending, OrderStatus::AwaitingPayment => 'warning',
                                OrderStatus::Paid, OrderStatus::Packed, OrderStatus::Shipped => 'info',
                                OrderStatus::Delivered, OrderStatus::Fulfilled => 'success',
                                OrderStatus::Cancelled, OrderStatus::Failed => 'danger',
                                OrderStatus::Refunded => 'gray',
                                OrderStatus::PartiallyRefunded => 'warning',
                            }),

                        TextEntry::make('user.name')
                            ->label('Customer'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ]),

                Section::make('Financial Breakdown')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('subtotal_cents')
                            ->label('Subtotal')
                            ->money(fn (Order $record): string => $record->currency ?? 'USD', divideBy: 100),

                        TextEntry::make('tax_cents')
                            ->label('Tax')
                            ->money(fn (Order $record): string => $record->currency ?? 'USD', divideBy: 100),

                        TextEntry::make('shipping_cost_cents')
                            ->label('Shipping')
                            ->money(fn (Order $record): string => $record->currency ?? 'USD', divideBy: 100),

                        TextEntry::make('total_cents')
                            ->label('Total')
                            ->money(fn (Order $record): string => $record->currency ?? 'USD', divideBy: 100)
                            ->weight('bold'),

                        TextEntry::make('refunded_amount_cents')
                            ->label('Refunded Amount')
                            ->money(fn (Order $record): string => $record->currency ?? 'USD', divideBy: 100)
                            ->color('danger'),

                        TextEntry::make('remaining_refundable')
                            ->label('Refundable Amount')
                            ->state(fn (Order $record): string => number_format($record->getRemainingRefundableAmount() / 100, 2))
                            ->prefix(fn (Order $record): string => match ($record->currency) {
                                'USD' => '$',
                                'EUR' => "\u{20AC}",
                                'GBP' => "\u{00A3}",
                                default => $record->currency.' ',
                            }),
                    ]),

                Section::make('Payment Intent')
                    ->relationship('paymentIntent')
                    ->schema([
                        TextEntry::make('provider')
                            ->label('Provider'),

                        TextEntry::make('provider_reference')
                            ->label('Reference')
                            ->copyable(),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('amount')
                            ->label('Amount')
                            ->money(fn ($record): string => $record?->currency ?? 'USD', divideBy: 100),

                        TextEntry::make('transaction_id')
                            ->label('Transaction ID')
                            ->copyable(),
                    ])
                    ->columns(3)
                    ->collapsed(),

                Section::make('Domain Event Timeline')
                    ->schema([
                        RepeatableEntry::make('domainEvents')
                            ->label('')
                            ->state(function (Order $record): array {
                                return DomainEventRecord::query()
                                    ->where('payload->order_id', $record->id)
                                    ->orWhere('payload->orderId', $record->id)
                                    ->orderBy('occurred_at', 'desc')
                                    ->get()
                                    ->map(fn (DomainEventRecord $event) => [
                                        'event_type' => class_basename($event->event_type),
                                        'occurred_at' => $event->occurred_at?->format('Y-m-d H:i:s'),
                                        'payload' => json_encode($event->payload, JSON_PRETTY_PRINT),
                                    ])
                                    ->toArray();
                            })
                            ->schema([
                                TextEntry::make('event_type')
                                    ->label('Event')
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('occurred_at')
                                    ->label('Timestamp'),

                                TextEntry::make('payload')
                                    ->label('Payload')
                                    ->markdown()
                                    ->columnSpanFull(),
                            ])
                            ->columns(),
                    ])
                    ->collapsed(),
            ]);
    }
}
