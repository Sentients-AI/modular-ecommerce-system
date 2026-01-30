<?php

declare(strict_types=1);

namespace App\Filament\Resources\Refunds\Schemas;

use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Models\Refund;
use App\Shared\Domain\DomainEventRecord;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class RefundInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Refund Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Refund ID'),

                        TextEntry::make('order.order_number')
                            ->label('Order')
                            ->url(fn (Refund $record): ?string => $record->order_id
                                ? route('filament.control-plane.resources.orders.view', ['record' => $record->order_id])
                                : null
                            ),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (RefundStatus $state): string => match ($state) {
                                RefundStatus::Requested, RefundStatus::PendingApproval => 'warning',
                                RefundStatus::Approved, RefundStatus::Processing => 'info',
                                RefundStatus::Succeeded => 'success',
                                RefundStatus::Failed, RefundStatus::Rejected, RefundStatus::Cancelled => 'danger',
                            }),

                        TextEntry::make('amount_cents')
                            ->label('Amount')
                            ->money(fn (Refund $record): string => $record->currency ?? 'USD', divideBy: 100),

                        TextEntry::make('currency')
                            ->label('Currency'),

                        TextEntry::make('reason')
                            ->label('Reason')
                            ->columnSpanFull(),

                        TextEntry::make('external_refund_id')
                            ->label('External Refund ID')
                            ->copyable()
                            ->visible(fn (Refund $record): bool => $record->external_refund_id !== null),

                        TextEntry::make('created_at')
                            ->label('Requested At')
                            ->dateTime(),

                        TextEntry::make('approvedBy.name')
                            ->label('Approved By')
                            ->visible(fn (Refund $record): bool => $record->approved_by !== null),

                        TextEntry::make('approved_at')
                            ->label('Approved At')
                            ->dateTime()
                            ->visible(fn (Refund $record): bool => $record->approved_at !== null),
                    ]),

                Section::make('Related Order')
                    ->relationship('order')
                    ->schema([
                        TextEntry::make('order_number')
                            ->label('Order Number'),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('total_cents')
                            ->label('Order Total')
                            ->money(fn ($record): string => $record?->currency ?? 'USD', divideBy: 100),

                        TextEntry::make('refunded_amount_cents')
                            ->label('Total Refunded')
                            ->money(fn ($record): string => $record?->currency ?? 'USD', divideBy: 100),
                    ])
                    ->columns(4)
                    ->collapsed(),

                Section::make('Refund Event Timeline')
                    ->schema([
                        RepeatableEntry::make('refundEvents')
                            ->label('')
                            ->state(function (Refund $record): array {
                                return DomainEventRecord::query()
                                    ->where('payload->refund_id', $record->id)
                                    ->orWhere('payload->refundId', $record->id)
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
