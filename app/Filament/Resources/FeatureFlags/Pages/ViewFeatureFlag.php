<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeatureFlags\Pages;

use App\Domain\FeatureFlag\Actions\DisableFeatureFlagAction;
use App\Domain\FeatureFlag\Actions\EnableFeatureFlagAction;
use App\Domain\FeatureFlag\Models\FeatureFlag;
use App\Filament\Resources\FeatureFlags\FeatureFlagResource;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

final class ViewFeatureFlag extends ViewRecord
{
    protected static string $resource = FeatureFlagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggle')
                ->label(fn (FeatureFlag $record): string => $record->is_enabled ? 'Disable' : 'Enable')
                ->icon(fn (FeatureFlag $record): string => $record->is_enabled ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn (FeatureFlag $record): string => $record->is_enabled ? 'danger' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn (FeatureFlag $record): string => $record->is_enabled ? 'Disable Feature Flag' : 'Enable Feature Flag')
                ->modalDescription(fn (FeatureFlag $record): string => $record->is_enabled
                    ? "Are you sure you want to disable '{$record->name}'? This may affect users currently using this feature."
                    : "Are you sure you want to enable '{$record->name}'?"
                )
                ->visible(fn (FeatureFlag $record): bool => auth()->user()->can('toggle', $record))
                ->action(function (FeatureFlag $record): void {
                    try {
                        if ($record->is_enabled) {
                            app(DisableFeatureFlagAction::class)->execute($record, auth()->user());
                            Notification::make()
                                ->title('Feature Flag Disabled')
                                ->body("'{$record->name}' has been disabled.")
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

                        $this->refreshFormData(['is_enabled', 'enabled_at', 'disabled_at']);
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Toggle Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
