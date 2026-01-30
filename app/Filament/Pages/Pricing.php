<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Product\Actions\ChangePriceAction;
use App\Domain\Product\Actions\SchedulePriceChangeAction;
use App\Domain\Product\DTOs\ChangePriceData;
use App\Domain\Product\Models\Product;
use BackedEnum;
use Carbon\Carbon;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class Pricing extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected string $view = 'filament.pages.pricing';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Pricing';

    protected static ?string $navigationLabel = 'Pricing';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->with(['category', 'priceHistories' => fn ($q) => $q->where('effective_at', '>', now())->limit(1)]))
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label('Product')
                    ->sortable()
                    ->searchable()
                    ->limit(30),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                TextColumn::make('price_cents')
                    ->label('Current Price')
                    ->money(fn ($record): string => $record->currency ?? 'USD', divideBy: 100)
                    ->sortable(),

                TextColumn::make('sale_price')
                    ->label('Sale Price')
                    ->money(fn ($record): string => $record->currency ?? 'USD', divideBy: 100)
                    ->placeholder('-')
                    ->color('success')
                    ->sortable(),

                TextColumn::make('scheduled_price')
                    ->label('Scheduled')
                    ->state(function (Product $record): ?string {
                        $scheduled = $record->priceHistories->first();
                        if (! $scheduled) {
                            return null;
                        }

                        return number_format($scheduled->new_price_cents / 100, 2).' @ '.$scheduled->effective_at->format('M j');
                    })
                    ->color('warning')
                    ->placeholder('-'),

                TextColumn::make('currency')
                    ->label('Currency')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('is_active')
                    ->label('Active')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('change_price')
                    ->label('Change Price')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->form([
                        TextInput::make('new_price_cents')
                            ->label('New Price (in cents)')
                            ->helperText(fn (?Product $record): string => $record ? "Current: {$record->price_cents} cents" : '')
                            ->numeric()
                            ->required()
                            ->minValue(0),

                        TextInput::make('new_sale_price')
                            ->label('New Sale Price (in cents)')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),

                        Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Change Price Now')
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
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('schedule_price')
                    ->label('Schedule')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->form([
                        TextInput::make('new_price_cents')
                            ->label('New Price (in cents)')
                            ->helperText(fn (?Product $record): string => $record ? "Current: {$record->price_cents} cents" : '')
                            ->numeric()
                            ->required()
                            ->minValue(0),

                        TextInput::make('new_sale_price')
                            ->label('New Sale Price (in cents)')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),

                        DateTimePicker::make('effective_at')
                            ->label('Effective At')
                            ->required()
                            ->minDate(now()->addMinutes(5))
                            ->native(false),

                        DateTimePicker::make('expires_at')
                            ->label('Expires At (optional)')
                            ->minDate(now()->addHour())
                            ->native(false),

                        Textarea::make('reason')
                            ->label('Reason')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Schedule Price Change')
                    ->action(function (Product $record, array $data): void {
                        try {
                            app(SchedulePriceChangeAction::class)->execute(new ChangePriceData(
                                productId: $record->id,
                                newPriceCents: (int) $data['new_price_cents'],
                                newSalePrice: $data['new_sale_price'] ? (int) $data['new_sale_price'] : null,
                                reason: $data['reason'],
                                effectiveAt: Carbon::parse($data['effective_at']),
                                expiresAt: $data['expires_at'] ? Carbon::parse($data['expires_at']) : null,
                                changedBy: auth()->id(),
                            ));

                            Notification::make()
                                ->title('Price Change Scheduled')
                                ->success()
                                ->send();
                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->paginated([10, 25, 50, 100]);
    }
}
