<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeatureFlags\Tables;

use App\Domain\FeatureFlag\Actions\DisableFeatureFlagAction;
use App\Domain\FeatureFlag\Actions\EnableFeatureFlagAction;
use App\Domain\FeatureFlag\Models\FeatureFlag;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class FeatureFlagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Key')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('enabled_at')
                    ->label('Enabled At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('enabledBy.name')
                    ->label('Enabled By')
                    ->placeholder('-'),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('key', 'asc')
            ->filters([
                TernaryFilter::make('is_enabled')
                    ->label('Status'),
            ])
            ->recordActions([
                Action::make('toggle')
                    ->label(fn (FeatureFlag $record): string => $record->is_enabled ? 'Disable' : 'Enable')
                    ->icon(fn (FeatureFlag $record): string => $record->is_enabled ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (FeatureFlag $record): string => $record->is_enabled ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (FeatureFlag $record): string => $record->is_enabled ? 'Disable Feature Flag' : 'Enable Feature Flag')
                    ->modalDescription(fn (FeatureFlag $record): string => $record->is_enabled
                        ? "Are you sure you want to disable '$record->name'? This may affect users currently using this feature."
                        : "Are you sure you want to enable '$record->name'?"
                    )
                    ->visible(fn (FeatureFlag $record): bool => auth()->user()->can('toggle', $record))
                    ->action(function (FeatureFlag $record): void {
                        try {
                            if ($record->is_enabled) {
                                app(DisableFeatureFlagAction::class)->execute($record, auth()->user());
                                Notification::make()
                                    ->title('Feature Flag Disabled')
                                    ->body("'$record->name' has been disabled.")
                                    ->warning()
                                    ->send();
                            } else {
                                app(EnableFeatureFlagAction::class)->execute($record, auth()->user());
                                Notification::make()
                                    ->title('Feature Flag Enabled')
                                    ->body("'{$record->name}' has been enabled.")
                                    ->success()
                                    ->send();
                            }
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Toggle Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                ViewAction::make(),
            ]);
    }
}
