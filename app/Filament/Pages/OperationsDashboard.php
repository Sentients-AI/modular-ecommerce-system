<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\FailedPaymentsWidget;
use App\Filament\Widgets\OrdersTodayWidget;
use App\Filament\Widgets\PaymentStatusWidget;
use App\Filament\Widgets\PendingRefundsWidget;
use App\Filament\Widgets\RevenueWidget;
use BackedEnum;
use Filament\Pages\Dashboard;
use Filament\Support\Icons\Heroicon;

final class OperationsDashboard extends Dashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string $routePath = '/';

    protected static ?string $title = 'Operations Dashboard';

    protected static ?int $navigationSort = 0;

    public function getWidgets(): array
    {
        return [
            OrdersTodayWidget::class,
            RevenueWidget::class,
            PaymentStatusWidget::class,
            PendingRefundsWidget::class,
            FailedPaymentsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
