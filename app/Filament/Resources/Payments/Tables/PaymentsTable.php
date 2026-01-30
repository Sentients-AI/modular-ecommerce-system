<?php

declare(strict_types=1);

namespace App\Filament\Resources\Payments\Tables;

use App\Domain\Payment\Enums\PaymentStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record): ?string => $record->order_id
                        ? route('filament.control-plane.resources.orders.view', ['record' => $record->order_id])
                        : null
                    ),

                TextColumn::make('provider')
                    ->label('Provider')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (PaymentStatus $state): string => match ($state) {
                        PaymentStatus::RequiresPayment => 'warning',
                        PaymentStatus::Processing => 'info',
                        PaymentStatus::Succeeded => 'success',
                        PaymentStatus::Failed, PaymentStatus::Cancelled => 'danger',
                    }),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record): string => $record->currency ?? 'USD', divideBy: 100)
                    ->sortable(),

                TextColumn::make('currency')
                    ->label('Currency')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('provider_reference')
                    ->label('Reference')
                    ->limit(20)
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(PaymentStatus::class),
                SelectFilter::make('provider')
                    ->options([
                        'stripe' => 'Stripe',
                        'paypal' => 'PayPal',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
