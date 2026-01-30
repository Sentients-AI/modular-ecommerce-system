<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Schemas;

use App\Domain\Category\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Category Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                if (! $get('slug') || $get('slug') === Str::slug($get('name_original') ?? '')) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Category::class, 'slug', ignoreRecord: true)
                            ->helperText('URL-friendly identifier. Auto-generated from name.'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Select::make('parent_id')
                            ->label('Parent Category')
                            ->options(fn (?Category $record) => Category::query()
                                ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                                ->whereNull('parent_id')
                                ->orWhere(fn ($query) => $query
                                    ->whereNotNull('parent_id')
                                    ->when(isset($record), fn ($q) => $q->where('id', '!=', $record->id))
                                )
                                ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->placeholder('None (Root Category)'),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lower numbers appear first.'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive categories are hidden from customers.'),
                    ]),
            ]);
    }
}
