<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Domain\Refund\Enums\RefundStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class RefundsRelationManager extends RelationManager
{
    protected static string $relationship = 'refunds';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

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
                    ->limit(30),

                TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('approved_at')
                    ->label('Approved')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
