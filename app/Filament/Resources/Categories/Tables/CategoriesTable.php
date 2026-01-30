<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Tables;

use App\Domain\Category\Models\Category;
use DomainException;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('parent.name')
                    ->label('Parent Category')
                    ->sortable()
                    ->placeholder('Root'),

                TextColumn::make('products_count')
                    ->label('Products')
                    ->counts('products')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('children_count')
                    ->label('Subcategories')
                    ->counts('children')
                    ->sortable()
                    ->alignRight(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status'),

                SelectFilter::make('parent_id')
                    ->label('Parent Category')
                    ->options(fn () => Category::query()
                        ->whereNull('parent_id')
                        ->pluck('name', 'id')
                        ->prepend('Root Categories', '')
                        ->toArray()
                    )
                    ->placeholder('All Categories'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (Category $record) {
                        if ($record->hasChildren()) {
                            throw new DomainException('Cannot delete a category that has child categories.');
                        }
                        if ($record->products()->exists()) {
                            throw new DomainException('Cannot delete a category that has products.');
                        }
                    }),
            ])
            ->bulkActions([]);
    }
}
