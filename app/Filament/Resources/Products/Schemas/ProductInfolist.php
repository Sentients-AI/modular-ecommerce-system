<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Domain\Product\Models\PriceHistory;
use App\Domain\Product\Models\Product;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('sku')
                            ->label('SKU')
                            ->copyable(),

                        TextEntry::make('name')
                            ->label('Name'),

                        TextEntry::make('slug')
                            ->label('Slug'),

                        TextEntry::make('category.name')
                            ->label('Category'),

                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),

                        TextEntry::make('is_featured')
                            ->label('Featured')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                            ->color(fn (bool $state): string => $state ? 'info' : 'gray'),

                        TextEntry::make('short_description')
                            ->label('Short Description')
                            ->columnSpanFull(),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->markdown(),
                    ]),

                Section::make('Pricing')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('price_cents')
                            ->label('Price')
                            ->money(fn (Product $record): string => $record->currency ?? 'USD', divideBy: 100),

                        TextEntry::make('sale_price')
                            ->label('Sale Price')
                            ->money(fn (Product $record): string => $record->currency ?? 'USD', divideBy: 100)
                            ->placeholder('No sale price')
                            ->color('success'),

                        TextEntry::make('currency')
                            ->label('Currency'),

                        TextEntry::make('discount_percentage')
                            ->label('Discount')
                            ->suffix('%')
                            ->placeholder('N/A')
                            ->color('success'),
                    ]),

                Section::make('Inventory')
                    ->relationship('stock')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('quantity_available')
                            ->label('Available')
                            ->color(fn ($state): string => $state <= 0 ? 'danger' : ($state <= 10 ? 'warning' : 'success')),

                        TextEntry::make('quantity_reserved')
                            ->label('Reserved')
                            ->color(fn ($state): string => $state > 0 ? 'info' : 'gray'),

                        TextEntry::make('net_available')
                            ->label('Net Available')
                            ->state(fn ($record): int => ($record->quantity_available ?? 0) - ($record->quantity_reserved ?? 0))
                            ->weight('bold'),
                    ]),

                Section::make('Price History')
                    ->schema([
                        RepeatableEntry::make('priceHistories')
                            ->label('')
                            ->state(function (Product $record): array {
                                return PriceHistory::query()
                                    ->where('product_id', $record->id)
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->get()
                                    ->map(fn (PriceHistory $history) => [
                                        'old_price' => $history->old_price_cents
                                            ? number_format($history->old_price_cents / 100, 2)
                                            : '-',
                                        'new_price' => number_format($history->new_price_cents / 100, 2),
                                        'reason' => $history->reason ?? '-',
                                        'effective_at' => $history->effective_at?->format('Y-m-d H:i'),
                                        'changed_by' => $history->changedBy?->name ?? 'System',
                                    ])
                                    ->toArray();
                            })
                            ->schema([
                                TextEntry::make('old_price')
                                    ->label('Old'),

                                TextEntry::make('new_price')
                                    ->label('New'),

                                TextEntry::make('reason')
                                    ->label('Reason'),

                                TextEntry::make('changed_by')
                                    ->label('By'),

                                TextEntry::make('effective_at')
                                    ->label('Effective'),
                            ])
                            ->columns(5),
                    ])
                    ->collapsed(),

                Section::make('SEO')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('meta_title')
                            ->label('Meta Title')
                            ->placeholder('Not set'),

                        TextEntry::make('meta_description')
                            ->label('Meta Description')
                            ->placeholder('Not set')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
