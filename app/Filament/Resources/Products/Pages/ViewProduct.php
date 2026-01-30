<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Domain\Product\Actions\ChangePriceAction;
use App\Domain\Product\DTOs\ChangePriceData;
use App\Domain\Product\Models\Product;
use App\Filament\Resources\Products\ProductResource;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

final class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('change_price')
                ->label('Change Price')
                ->icon('heroicon-o-currency-dollar')
                ->color('warning')
                ->form([
                    TextInput::make('new_price_cents')
                        ->label('New Price (in cents)')
                        ->helperText(fn (Product $record): string => "Current price: {$record->price_cents} cents")
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    TextInput::make('new_sale_price')
                        ->label('New Sale Price (in cents, optional)')
                        ->helperText(fn (Product $record): string => $record->sale_price
                            ? "Current sale price: {$record->sale_price} cents"
                            : 'No current sale price'
                        )
                        ->numeric()
                        ->minValue(0)
                        ->nullable(),

                    Textarea::make('reason')
                        ->label('Reason for Change')
                        ->required()
                        ->maxLength(500),
                ])
                ->requiresConfirmation()
                ->modalHeading('Change Product Price')
                ->modalDescription('This action will update the product price immediately and create a price history record.')
                ->visible(fn (Product $record): bool => auth()->user()->can('changePrice', $record))
                ->action(function (Product $record, array $data): void {
                    try {
                        app(ChangePriceAction::class)->execute(new ChangePriceData(
                            productId: $record->id,
                            newPriceCents: (int) $data['new_price_cents'],
                            newSalePrice: $data['new_sale_price'] ? (int) $data['new_sale_price'] : null,
                            reason: $data['reason'],
                            changedBy: auth()->id(),
                        ));

                        Notification::make()
                            ->title('Price Updated')
                            ->body("Price for {$record->name} has been updated successfully.")
                            ->success()
                            ->send();

                        $this->refreshFormData(['price_cents', 'sale_price']);
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Price Change Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            EditAction::make(),
        ];
    }
}
