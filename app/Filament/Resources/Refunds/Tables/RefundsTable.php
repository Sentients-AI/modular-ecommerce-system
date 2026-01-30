<?php

declare(strict_types=1);

namespace App\Filament\Resources\Refunds\Tables;

use App\Domain\Refund\Enums\RefundStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class RefundsTable
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

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (RefundStatus $state): string => match ($state) {
                        RefundStatus::Requested, RefundStatus::PendingApproval => 'warning',
                        RefundStatus::Approved, RefundStatus::Processing => 'info',
                        RefundStatus::Succeeded => 'success',
                        RefundStatus::Failed, RefundStatus::Rejected, RefundStatus::Cancelled => 'danger',
                    }),

                TextColumn::make('amount_cents')
                    ->label('Amount')
                    ->money(fn ($record): string => $record->currency ?? 'USD', divideBy: 100)
                    ->sortable(),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('approved_at')
                    ->label('Approved')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(RefundStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
