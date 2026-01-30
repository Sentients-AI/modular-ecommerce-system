<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class InventoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->sortable()
                    ->searchable()
                    ->limit(40),

                TextColumn::make('quantity_available')
                    ->label('Available')
                    ->sortable()
                    ->alignRight()
                    ->color(fn ($state): string => $state <= 0 ? 'danger' : ($state <= 10 ? 'warning' : 'success')),

                TextColumn::make('quantity_reserved')
                    ->label('Reserved')
                    ->sortable()
                    ->alignRight()
                    ->color(fn ($state): string => $state > 0 ? 'info' : 'gray'),

                TextColumn::make('net_available')
                    ->label('Net Available')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw('(quantity_available - quantity_reserved) '.$direction);
                    })
                    ->alignRight()
                    ->state(fn ($record): int => $record->quantity_available - $record->quantity_reserved)
                    ->color(fn ($state): string => $state <= 0 ? 'danger' : ($state <= 5 ? 'warning' : 'success')),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Filter::make('low_stock')
                    ->label('Low Stock (<= 10)')
                    ->query(fn (Builder $query): Builder => $query->where('quantity_available', '<=', 10)),

                Filter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->query(fn (Builder $query): Builder => $query->whereRaw('quantity_available - quantity_reserved <= 0')),

                Filter::make('has_reservations')
                    ->label('Has Reservations')
                    ->query(fn (Builder $query): Builder => $query->where('quantity_reserved', '>', 0)),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
