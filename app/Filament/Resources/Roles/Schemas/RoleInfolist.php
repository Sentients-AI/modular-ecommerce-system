<?php

declare(strict_types=1);

namespace App\Filament\Resources\Roles\Schemas;

use App\Domain\Role\Models\Role;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),

                        TextEntry::make('name')
                            ->label('Name')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'admin' => 'danger',
                                'finance' => 'warning',
                                'support' => 'info',
                                default => 'gray',
                            }),

                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description'),

                        TextEntry::make('users_count')
                            ->label('Total Users')
                            ->state(fn (Role $record): int => $record->users()->count()),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime(),
                    ]),

                Section::make('Users with this Role')
                    ->schema([
                        RepeatableEntry::make('users')
                            ->label('')
                            ->state(function (Role $record): array {
                                return $record->users()
                                    ->orderBy('name')
                                    ->limit(50)
                                    ->get()
                                    ->map(fn ($user) => [
                                        'id' => $user->id,
                                        'name' => $user->name,
                                        'email' => $user->email,
                                        'created_at' => $user->created_at?->format('Y-m-d H:i'),
                                    ])
                                    ->toArray();
                            })
                            ->schema([
                                TextEntry::make('id')
                                    ->label('ID'),

                                TextEntry::make('name')
                                    ->label('Name'),

                                TextEntry::make('email')
                                    ->label('Email')
                                    ->copyable(),

                                TextEntry::make('created_at')
                                    ->label('Created'),
                            ])
                            ->columns(4),
                    ])
                    ->collapsed(),
            ]);
    }
}
