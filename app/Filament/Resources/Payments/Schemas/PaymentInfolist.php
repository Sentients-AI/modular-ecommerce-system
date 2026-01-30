<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payments\Schemas;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntent;
use App\Shared\Domain\DomainEventRecord;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Payment ID'),

                        TextEntry::make('order.order_number')
                            ->label('Order')
                            ->url(fn (PaymentIntent $record): ?string => $record->order_id
                                ? route('filament.control-plane.resources.orders.view', ['record' => $record->order_id])
                                : null
                            ),

                        TextEntry::make('provider')
                            ->label('Provider')
                            ->badge(),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (PaymentStatus $state): string => match ($state) {
                                PaymentStatus::RequiresPayment => 'warning',
                                PaymentStatus::Processing => 'info',
                                PaymentStatus::Succeeded => 'success',
                                PaymentStatus::Failed, PaymentStatus::Cancelled => 'danger',
                            }),

                        TextEntry::make('amount')
                            ->label('Amount')
                            ->money(fn (PaymentIntent $record): string => $record->currency ?? 'USD', divideBy: 100),

                        TextEntry::make('currency')
                            ->label('Currency'),

                        TextEntry::make('provider_reference')
                            ->label('Provider Reference')
                            ->copyable(),

                        TextEntry::make('transaction_id')
                            ->label('Transaction ID')
                            ->copyable(),

                        TextEntry::make('idempotency_key')
                            ->label('Idempotency Key')
                            ->copyable(),

                        TextEntry::make('attempts')
                            ->label('Attempts'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),

                        TextEntry::make('expires_at')
                            ->label('Expires')
                            ->dateTime(),

                        TextEntry::make('failed_at')
                            ->label('Failed At')
                            ->dateTime()
                            ->visible(fn (PaymentIntent $record): bool => $record->failed_at !== null),
                    ]),

                Section::make('Gateway Response')
                    ->schema([
                        KeyValueEntry::make('gateway_response')
                            ->label('')
                            ->state(fn (PaymentIntent $record): array => is_array($record->gateway_response) ? $record->gateway_response : [])
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->visible(fn (PaymentIntent $record): bool => is_array($record->gateway_response) && ! empty($record->gateway_response)),

                Section::make('Metadata')
                    ->schema([
                        KeyValueEntry::make('metadata')
                            ->label('')
                            ->state(fn (PaymentIntent $record): array => is_array($record->metadata) ? $record->metadata : [])
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->visible(fn (PaymentIntent $record): bool => is_array($record->metadata) && ! empty($record->metadata)),

                Section::make('Payment Event Timeline')
                    ->schema([
                        RepeatableEntry::make('paymentEvents')
                            ->label('')
                            ->state(function (PaymentIntent $record): array {
                                return DomainEventRecord::query()
                                    ->where('payload->payment_intent_id', $record->id)
                                    ->orWhere('payload->paymentIntentId', $record->id)
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
