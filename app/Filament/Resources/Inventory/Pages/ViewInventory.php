<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventory\Pages;

use App\Domain\Inventory\Actions\AdjustStockAction;
use App\Domain\Inventory\Actions\ReconcileStockAction;
use App\Domain\Inventory\DTOs\AdjustStockData;
use App\Domain\Inventory\DTOs\ReconcileStockData;
use App\Domain\Inventory\Models\Stock;
use App\Filament\Resources\Inventory\InventoryResource;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

final class ViewInventory extends ViewRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('adjust_stock')
                ->label('Adjust Stock')
                ->icon('heroicon-o-adjustments-horizontal')
                ->color('warning')
                ->form([
                    TextInput::make('quantity_change')
                        ->label('Quantity Change')
                        ->helperText('Use positive numbers to add stock, negative to remove.')
                        ->numeric()
                        ->required(),

                    Textarea::make('reason')
                        ->label('Reason')
                        ->required()
                        ->maxLength(500),
                ])
                ->requiresConfirmation()
                ->modalHeading('Adjust Stock')
                ->modalDescription('This action will adjust the stock quantity and create a movement record.')
                ->visible(fn (Stock $record): bool => auth()->user()->can('adjust', $record))
                ->action(function (Stock $record, array $data): void {
                    try {
                        app(AdjustStockAction::class)->execute(new AdjustStockData(
                            productId: (string) $record->product_id,
                            quantityChange: (int) $data['quantity_change'],
                            reason: $data['reason'],
                            userId: (string) auth()->id(),
                        ));

                        Notification::make()
                            ->title('Stock Adjusted')
                            ->body("Stock for {$record->product->name} has been adjusted.")
                            ->success()
                            ->send();

                        $this->refreshFormData(['quantity_available', 'quantity_reserved']);
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Adjustment Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('reconcile_stock')
                ->label('Reconcile Stock')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->form([
                    TextInput::make('actual_count')
                        ->label('Actual Physical Count')
                        ->helperText('Enter the actual physical count of this product.')
                        ->numeric()
                        ->minValue(0)
                        ->required(),

                    Textarea::make('reason')
                        ->label('Reason for Reconciliation')
                        ->required()
                        ->maxLength(500),
                ])
                ->requiresConfirmation()
                ->modalHeading('Reconcile Stock')
                ->modalDescription('This action will set the stock quantity to match the physical count.')
                ->visible(fn (Stock $record): bool => auth()->user()->can('reconcile', $record))
                ->action(function (Stock $record, array $data): void {
                    try {
                        app(ReconcileStockAction::class)->execute(new ReconcileStockData(
                            productId: (string) $record->product_id,
                            actualCount: (int) $data['actual_count'],
                            reason: $data['reason'],
                            userId: (string) auth()->id(),
                        ));

                        Notification::make()
                            ->title('Stock Reconciled')
                            ->body("Stock for {$record->product->name} has been reconciled.")
                            ->success()
                            ->send();

                        $this->refreshFormData(['quantity_available', 'quantity_reserved']);
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Reconciliation Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
