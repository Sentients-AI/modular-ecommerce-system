<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Models\Refund;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

final class PendingRefundsWidget extends TableWidget
{
    protected static ?string $heading = 'Pending Refunds';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Refund::query()
                    ->whereIn('status', [
                        RefundStatus::Requested,
                        RefundStatus::PendingApproval,
                        RefundStatus::Approved,
                    ])
                    ->orderBy('created_at', 'asc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('id')
                    ->label('ID'),

                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->url(fn ($record): ?string => $record->order_id
                        ? route('filament.control-plane.resources.orders.view', ['record' => $record->order_id])
                        : null
                    ),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (RefundStatus $state): string => match ($state) {
                        RefundStatus::Requested, RefundStatus::PendingApproval => 'warning',
                        RefundStatus::Approved => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('amount_cents')
                    ->label('Amount')
                    ->money(fn ($record): string => $record->currency ?? 'USD', divideBy: 100),

                TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->since(),

                TextColumn::make('waiting_time')
                    ->label('Waiting')
                    ->state(fn (Refund $record): string => $record->created_at->diffForHumans(short: true))
                    ->color(fn (Refund $record): string => $record->created_at->diffInMinutes() > 60 ? 'danger' : 'gray'),
            ])
            ->paginated(false);
    }
}
