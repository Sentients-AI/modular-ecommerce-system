<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Domain\Order\Enums\OrderStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('order_number')
                    ->label('Order #')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (OrderStatus $state): string => match ($state) {
                        OrderStatus::Pending, OrderStatus::AwaitingPayment => 'warning',
                        OrderStatus::Paid, OrderStatus::Packed, OrderStatus::Shipped => 'info',
                        OrderStatus::Delivered, OrderStatus::Fulfilled => 'success',
                        OrderStatus::Cancelled, OrderStatus::Failed => 'danger',
                        OrderStatus::Refunded => 'gray',
                        OrderStatus::PartiallyRefunded => 'warning',
                    }),

                TextColumn::make('total_cents')
                    ->label('Total')
                    ->money(fn ($record): string => $record->currency ?? 'USD', divideBy: 100)
                    ->sortable(),

                TextColumn::make('refunded_amount_cents')
                    ->label('Refunded')
                    ->money(fn ($record): string => $record->currency ?? 'USD', divideBy: 100)
                    ->sortable(),

                TextColumn::make('currency')
                    ->label('Currency')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
