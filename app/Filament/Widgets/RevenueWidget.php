<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class RevenueWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $paidStatuses = [
            OrderStatus::Paid,
            OrderStatus::Packed,
            OrderStatus::Shipped,
            OrderStatus::Delivered,
            OrderStatus::Fulfilled,
            OrderStatus::PartiallyRefunded,
        ];

        $totalRevenue = Order::query()
            ->whereIn('status', $paidStatuses)
            ->sum('total_cents');

        $totalRefunded = Order::query()
            ->where('refunded_amount_cents', '>', 0)
            ->sum('refunded_amount_cents');

        $netRevenue = $totalRevenue - $totalRefunded;

        $todayRevenue = Order::query()
            ->where('created_at', '>=', now()->startOfDay())
            ->whereIn('status', $paidStatuses)
            ->sum('total_cents');

        return [
            Stat::make('Total Revenue', '$'.number_format($totalRevenue / 100, 2))
                ->description('Lifetime gross revenue')
                ->color('primary')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Total Refunded', '$'.number_format($totalRefunded / 100, 2))
                ->description('Total refunded amount')
                ->color('danger')
                ->icon('heroicon-o-receipt-refund'),

            Stat::make('Net Revenue', '$'.number_format($netRevenue / 100, 2))
                ->description('After refunds')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Revenue Today', '$'.number_format($todayRevenue / 100, 2))
                ->description('Today\'s gross')
                ->color('info')
                ->icon('heroicon-o-calendar'),
        ];
    }
}
