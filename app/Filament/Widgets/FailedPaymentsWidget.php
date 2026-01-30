<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntent;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

final class FailedPaymentsWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Failed Payments';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PaymentIntent::query()
                    ->where('status', PaymentStatus::Failed)
                    ->where('failed_at', '>=', now()->subDays(7))
                    ->orderBy('failed_at', 'desc')
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

                TextColumn::make('provider')
                    ->label('Provider')
                    ->badge(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record): string => $record->currency ?? 'USD', divideBy: 100),

                TextColumn::make('attempts')
                    ->label('Attempts'),

                TextColumn::make('failed_at')
                    ->label('Failed At')
                    ->dateTime()
                    ->since(),
            ])
            ->paginated(false);
    }
}
