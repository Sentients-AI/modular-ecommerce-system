<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Inventory\Models\Stock;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\PaymentIntent;
use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Models\Refund;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

final class Anomalies extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected string $view = 'filament.pages.anomalies';

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Anomalies';

    public static function getNavigationBadge(): ?string
    {
        $page = new self;
        $count = $page->getTotalAnomalies();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function getStuckPaymentPendingOrders(): Collection
    {
        return Order::query()
            ->whereIn('status', [OrderStatus::AwaitingPayment, OrderStatus::Pending])
            ->where('created_at', '<', now()->subMinutes(30))
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();
    }

    public function getStuckApprovedRefunds(): Collection
    {
        return Refund::query()
            ->where('status', RefundStatus::Approved)
            ->where('approved_at', '<', now()->subMinutes(15))
            ->orderBy('approved_at', 'asc')
            ->limit(50)
            ->get();
    }

    public function getPaymentAmountMismatches(): Collection
    {
        return PaymentIntent::query()
            ->whereHas('order', function ($query) {
                $query->whereRaw('orders.total_cents != payment_intents.amount');
            })
            ->with('order')
            ->limit(50)
            ->get();
    }

    public function getStuckProcessingRefunds(): Collection
    {
        return Refund::query()
            ->where('status', RefundStatus::Processing)
            ->where('updated_at', '<', now()->subMinutes(5))
            ->orderBy('updated_at', 'asc')
            ->limit(50)
            ->get();
    }

    public function getOrdersWithoutPaymentIntent(): Collection
    {
        return Order::query()
            ->whereIn('status', [OrderStatus::AwaitingPayment])
            ->whereDoesntHave('paymentIntent')
            ->where('created_at', '<', now()->subMinutes(5))
            ->limit(50)
            ->get();
    }

    public function getNegativeStockProducts(): Collection
    {
        return Stock::query()
            ->with('product')
            ->whereRaw('quantity_reserved > quantity_available')
            ->limit(50)
            ->get();
    }

    public function getStaleReservations(): Collection
    {
        return Stock::query()
            ->with('product')
            ->where('quantity_reserved', '>', 0)
            ->whereHas('product', function ($query) {
                $query->whereDoesntHave('stock', function ($q) {
                    $q->whereHas('movements', function ($m) {
                        $m->where('type', 'reservation')
                            ->where('created_at', '>', now()->subHours(24));
                    });
                });
            })
            ->limit(50)
            ->get();
    }

    public function getTotalAnomalies(): int
    {
        return $this->getStuckPaymentPendingOrders()->count()
            + $this->getStuckApprovedRefunds()->count()
            + $this->getPaymentAmountMismatches()->count()
            + $this->getStuckProcessingRefunds()->count()
            + $this->getOrdersWithoutPaymentIntent()->count()
            + $this->getNegativeStockProducts()->count()
            + $this->getStaleReservations()->count();
    }
}
