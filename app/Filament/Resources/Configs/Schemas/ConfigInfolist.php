<?php

declare(strict_types=1);

namespace App\Filament\Resources\Configs\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ConfigInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Configuration Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('group')
                            ->label('Group')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'tax' => 'success',
                                'shipping' => 'info',
                                'payment' => 'warning',
                                default => 'gray',
                            }),

                        TextEntry::make('key')
                            ->label('Key')
                            ->copyable()
                            ->fontFamily('mono'),

                        TextEntry::make('name')
                            ->label('Name'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->placeholder('No description'),
                    ]),

                Section::make('Value')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('display_value')
                            ->label('Current Value')
                            ->fontFamily('mono'),

                        TextEntry::make('default_value')
                            ->label('Default Value')
                            ->fontFamily('mono')
                            ->placeholder('No default'),

                        TextEntry::make('type')
                            ->label('Type')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('is_sensitive')
                            ->label('Sensitive')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Yes (masked)' : 'No')
                            ->color(fn (bool $state): string => $state ? 'danger' : 'gray'),
                    ]),

                Section::make('Validation')
                    ->schema([
                        KeyValueEntry::make('validation_rules')
                            ->label('Validation Rules')
                            ->placeholder('No validation rules'),
                    ])
                    ->collapsed(),

                Section::make('Timestamps')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ]),
            ]);
    }
}
