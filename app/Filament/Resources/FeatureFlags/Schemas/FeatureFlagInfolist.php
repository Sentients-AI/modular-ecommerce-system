<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeatureFlags\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class FeatureFlagInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Feature Flag Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('key')
                            ->label('Key')
                            ->copyable()
                            ->fontFamily('mono'),

                        TextEntry::make('name')
                            ->label('Name'),

                        TextEntry::make('is_enabled')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Enabled' : 'Disabled')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->placeholder('No description'),
                    ]),

                Section::make('Enable/Disable History')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('enabled_at')
                            ->label('Last Enabled')
                            ->dateTime()
                            ->placeholder('Never enabled'),

                        TextEntry::make('enabledBy.name')
                            ->label('Enabled By')
                            ->placeholder('-'),

                        TextEntry::make('disabled_at')
                            ->label('Last Disabled')
                            ->dateTime()
                            ->placeholder('Never disabled'),

                        TextEntry::make('disabledBy.name')
                            ->label('Disabled By')
                            ->placeholder('-'),
                    ]),

                Section::make('Conditions')
                    ->schema([
                        KeyValueEntry::make('conditions')
                            ->label('')
                            ->placeholder('No conditions set (applies to all)'),
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
