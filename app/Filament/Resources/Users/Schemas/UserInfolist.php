<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),

                        TextEntry::make('name')
                            ->label('Name'),

                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),

                        TextEntry::make('role.name')
                            ->label('Role')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'admin' => 'danger',
                                'finance' => 'warning',
                                'support' => 'info',
                                default => 'gray',
                            }),

                        TextEntry::make('email_verified_at')
                            ->label('Email Verified')
                            ->dateTime()
                            ->placeholder('Not verified'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ]),

                Section::make('Role Details')
                    ->relationship('role')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Role Name'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
