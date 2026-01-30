<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Shared\Domain\AuditLog;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class AuditLogPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected string $view = 'filament.pages.audit-log';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Audit Log';

    protected static ?string $navigationLabel = 'Audit Log';

    public function table(Table $table): Table
    {
        return $table
            ->query(AuditLog::query())
            ->columns([
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('actor_name')
                    ->label('Actor')
                    ->searchable(),

                TextColumn::make('actor_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'user' => 'info',
                        'system' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('action')
                    ->label('Action')
                    ->searchable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('target_type')
                    ->label('Target')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-'),

                TextColumn::make('target_id')
                    ->label('Target ID'),

                TextColumn::make('result')
                    ->label('Result')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failure' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('actor_type')
                    ->options([
                        'user' => 'User',
                        'system' => 'System',
                    ]),
                SelectFilter::make('result')
                    ->options([
                        'success' => 'Success',
                        'failure' => 'Failure',
                    ]),
            ])
            ->paginated([10, 25, 50, 100]);
    }
}
