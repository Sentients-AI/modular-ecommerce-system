<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntent;
use Filament\Widgets\ChartWidget;

final class PaymentStatusWidget extends ChartWidget
{
    protected ?string $heading = 'Payment Status (Last 7 Days)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $startDate = now()->subDays(7)->startOfDay();

        $succeeded = PaymentIntent::query()
            ->where('created_at', '>=', $startDate)
            ->where('status', PaymentStatus::Succeeded)
            ->count();

        $failed = PaymentIntent::query()
            ->where('created_at', '>=', $startDate)
            ->where('status', PaymentStatus::Failed)
            ->count();

        $processing = PaymentIntent::query()
            ->where('created_at', '>=', $startDate)
            ->whereIn('status', [PaymentStatus::RequiresPayment, PaymentStatus::Processing])
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Payments',
                    'data' => [$succeeded, $failed, $processing],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',   // success - green
                        'rgb(239, 68, 68)',   // danger - red
                        'rgb(234, 179, 8)',   // warning - yellow
                    ],
                ],
            ],
            'labels' => ['Succeeded', 'Failed', 'Processing'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
