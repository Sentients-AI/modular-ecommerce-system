<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Schemas;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockMovement;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class InventoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Information')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('product.sku')
                            ->label('SKU')
                            ->copyable(),

                        TextEntry::make('product.name')
                            ->label('Product Name'),

                        TextEntry::make('product.category.name')
                            ->label('Category'),
                    ]),

                Section::make('Stock Levels')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('quantity_available')
                            ->label('Available Quantity')
                            ->size('lg')
                            ->color(fn (Stock $record): string => $record->quantity_available <= 0 ? 'danger' : ($record->quantity_available <= 10 ? 'warning' : 'success')),

                        TextEntry::make('quantity_reserved')
                            ->label('Reserved Quantity')
                            ->size('lg')
                            ->color(fn (Stock $record): string => $record->quantity_reserved > 0 ? 'info' : 'gray'),

                        TextEntry::make('net_available')
                            ->label('Net Available')
                            ->state(fn (Stock $record): int => $record->quantity_available - $record->quantity_reserved)
                            ->size('lg')
                            ->weight('bold')
                            ->color(fn (Stock $record): string => ! $record->isInStock() ? 'danger' : 'success'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ]),

                Section::make('Recent Stock Movements')
                    ->schema([
                        RepeatableEntry::make('movements')
                            ->label('')
                            ->state(function (Stock $record): array {
                                return StockMovement::query()
                                    ->where('stock_id', $record->id)
                                    ->orderBy('created_at', 'desc')
                                    ->limit(20)
                                    ->get()
                                    ->map(fn (StockMovement $movement) => [
                                        'type' => $movement->type,
                                        'quantity' => $movement->quantity,
                                        'reason' => $movement->reason ?? '-',
                                        'created_at' => $movement->created_at?->format('Y-m-d H:i:s'),
                                        'user' => $movement->user?->name ?? 'System',
                                    ])
                                    ->toArray();
                            })
                            ->schema([
                                TextEntry::make('type')
                                    ->label('Type')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'reservation' => 'warning',
                                        'release' => 'info',
                                        'adjustment_in', 'reconciliation' => 'success',
                                        'adjustment_out', 'fulfillment' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('quantity')
                                    ->label('Qty'),

                                TextEntry::make('reason')
                                    ->label('Reason'),

                                TextEntry::make('user')
                                    ->label('By'),

                                TextEntry::make('created_at')
                                    ->label('Date'),
                            ])
                            ->columns(5),
                    ])
                    ->collapsed(),
            ]);
    }
}
