<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class OrdersTodayWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->startOfDay();

        $ordersToday = Order::query()
            ->where('created_at', '>=', $today)
            ->count();

        $paidOrdersToday = Order::query()
            ->where('created_at', '>=', $today)
            ->whereIn('status', [
                OrderStatus::Paid,
                OrderStatus::Packed,
                OrderStatus::Shipped,
                OrderStatus::Delivered,
                OrderStatus::Fulfilled,
            ])
            ->count();

        $pendingOrdersToday = Order::query()
            ->where('created_at', '>=', $today)
            ->whereIn('status', [
                OrderStatus::Pending,
                OrderStatus::AwaitingPayment,
            ])
            ->count();

        $cancelledOrdersToday = Order::query()
            ->where('created_at', '>=', $today)
            ->whereIn('status', [
                OrderStatus::Cancelled,
                OrderStatus::Failed,
            ])
            ->count();

        return [
            Stat::make('Orders Today', $ordersToday)
                ->description('Total orders placed today')
                ->color('primary')
                ->icon('heroicon-o-shopping-cart'),

            Stat::make('Paid Orders', $paidOrdersToday)
                ->description('Successfully paid')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Pending Orders', $pendingOrdersToday)
                ->description('Awaiting payment')
                ->color('warning')
                ->icon('heroicon-o-clock'),

            Stat::make('Failed/Cancelled', $cancelledOrdersToday)
                ->description('Cancelled or failed')
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
        ];
    }
}
