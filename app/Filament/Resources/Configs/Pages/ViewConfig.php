<?php

declare(strict_types=1);

namespace App\Filament\Resources\Configs\Pages;

use App\Domain\Config\Actions\UpdateConfigAction;
use App\Domain\Config\DTOs\UpdateConfigData;
use App\Domain\Config\Models\SystemConfig;
use App\Filament\Resources\Configs\ConfigResource;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

final class ViewConfig extends ViewRecord
{
    protected static string $resource = ConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('update_value')
                ->label('Update Value')
                ->icon('heroicon-o-pencil')
                ->color('warning')
                ->form(fn (SystemConfig $record): array => $this->getFormSchema($record))
                ->fillForm(fn (SystemConfig $record): array => [
                    'value' => $record->value ?? $record->default_value,
                ])
                ->requiresConfirmation()
                ->modalHeading('Update Configuration Value')
                ->visible(fn (SystemConfig $record): bool => auth()->user()->can('updateValue', $record))
                ->action(function (SystemConfig $record, array $data): void {
                    try {
                        app(UpdateConfigAction::class)->execute(new UpdateConfigData(
                            group: $record->group,
                            key: $record->key,
                            value: $data['value'],
                            updatedBy: auth()->id(),
                        ));

                        Notification::make()
                            ->title('Configuration Updated')
                            ->body("'{$record->name}' has been updated.")
                            ->success()
                            ->send();

                        $this->refreshFormData(['value']);
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Update Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    private function getFormSchema(SystemConfig $record): array
    {
        return match ($record->type) {
            'boolean', 'bool' => [
                Toggle::make('value')
                    ->label('Value')
                    ->helperText($record->description),
            ],
            'integer', 'int' => [
                TextInput::make('value')
                    ->label('Value')
                    ->numeric()
                    ->helperText($record->description),
            ],
            'float', 'decimal' => [
                TextInput::make('value')
                    ->label('Value')
                    ->numeric()
                    ->step(0.01)
                    ->helperText($record->description),
            ],
            'json', 'array' => [
                Textarea::make('value')
                    ->label('Value (JSON)')
                    ->rows(5)
                    ->helperText($record->description),
            ],
            default => [
                TextInput::make('value')
                    ->label('Value')
                    ->helperText($record->description)
                    ->password($record->is_sensitive),
            ],
        };
    }
}
