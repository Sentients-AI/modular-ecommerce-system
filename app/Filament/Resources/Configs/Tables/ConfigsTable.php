<?php

declare(strict_types=1);

namespace App\Filament\Resources\Configs\Tables;

use App\Domain\Config\Actions\UpdateConfigAction;
use App\Domain\Config\DTOs\UpdateConfigData;
use App\Domain\Config\Models\SystemConfig;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

final class ConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')
                    ->label('Group')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tax' => 'success',
                        'shipping' => 'info',
                        'payment' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('key')
                    ->label('Key')
                    ->sortable()
                    ->searchable()
                    ->fontFamily('mono'),

                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('display_value')
                    ->label('Value')
                    ->limit(30),

                TextColumn::make('type')
                    ->label('Type')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_sensitive')
                    ->label('Sensitive')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Group::make('group')
                    ->label('Group')
                    ->collapsible(),
            ])
            ->defaultGroup('group')
            ->defaultSort('group', 'asc')
            ->filters([
                SelectFilter::make('group')
                    ->options(fn () => SystemConfig::query()
                        ->distinct()
                        ->pluck('group', 'group')
                        ->toArray()
                    ),

                SelectFilter::make('type')
                    ->options([
                        'string' => 'String',
                        'integer' => 'Integer',
                        'float' => 'Float',
                        'boolean' => 'Boolean',
                        'json' => 'JSON',
                    ]),
            ])
            ->recordActions([
                Action::make('update_value')
                    ->label('Update')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->form(fn (SystemConfig $record): array => self::getFormSchema($record))
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
                                ->body("'$record->name' has been updated.")
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Update Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                ViewAction::make(),
            ]);
    }

    private static function getFormSchema(SystemConfig $record): array
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
