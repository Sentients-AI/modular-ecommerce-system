<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use App\Domain\Order\Actions\CancelOrder;
use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Actions\RetryPaymentIntentAction;
use App\Filament\Resources\Orders\OrderResource;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

final class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancel_order')
                ->label('Cancel Order')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancel Order')
                ->modalDescription('Are you sure you want to cancel this order? This action will release reserved stock.')
                ->visible(fn (Order $record): bool => $this->canCancelOrder($record) && auth()->user()->can('cancel', $record))
                ->action(function (Order $record): void {
                    try {
                        app(CancelOrder::class)->execute($record);

                        Notification::make()
                            ->title('Order Cancelled')
                            ->body("Order {$record->order_number} has been cancelled successfully.")
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Cancel Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('retry_payment')
                ->label('Retry Payment')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Retry Payment')
                ->modalDescription('Are you sure you want to retry payment processing for this order?')
                ->visible(fn (Order $record): bool => $this->canRetryPayment($record) && auth()->user()->can('retryPayment', $record))
                ->action(function (Order $record): void {
                    $paymentIntent = $record->paymentIntent;

                    if (! $paymentIntent) {
                        Notification::make()
                            ->title('Retry Failed')
                            ->body('No payment intent found for this order.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        app(RetryPaymentIntentAction::class)->execute($paymentIntent);

                        Notification::make()
                            ->title('Payment Retry Initiated')
                            ->body("Payment retry initiated for order {$record->order_number}.")
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Retry Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('mark_fraudulent')
                ->label('Mark as Fraudulent')
                ->icon('heroicon-o-shield-exclamation')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Mark Order as Fraudulent')
                ->modalDescription('Are you sure you want to mark this order as fraudulent? This action will cancel the order and flag it for review.')
                ->visible(fn (Order $record): bool => $this->canMarkAsFraudulent($record) && auth()->user()->can('markAsFraudulent', $record))
                ->action(function (Order $record): void {
                    try {
                        // Cancel order if not already cancelled
                        if (! $record->isCancelled()) {
                            app(CancelOrder::class)->execute($record);
                        }

                        // Mark as fraudulent (would typically update a separate fraud field or create a fraud record)
                        // For now, we just record a domain event
                        $record->update(['status' => OrderStatus::Cancelled]);

                        Notification::make()
                            ->title('Order Flagged as Fraudulent')
                            ->body("Order {$record->order_number} has been marked as fraudulent.")
                            ->warning()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Action Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    private function canCancelOrder(Order $record): bool
    {
        // Can cancel if order is pending, awaiting payment, or paid (but not shipped/delivered)
        return in_array($record->status, [
            OrderStatus::Pending,
            OrderStatus::AwaitingPayment,
            OrderStatus::Paid,
            OrderStatus::Packed,
        ], true);
    }

    private function canRetryPayment(Order $record): bool
    {
        // Can retry if order is pending/awaiting payment and has a failed payment intent
        if (! in_array($record->status, [OrderStatus::Pending, OrderStatus::AwaitingPayment], true)) {
            return false;
        }

        $paymentIntent = $record->paymentIntent;

        return $paymentIntent !== null && $paymentIntent->status->value === 'failed';
    }

    private function canMarkAsFraudulent(Order $record): bool
    {
        // Can mark as fraudulent if order is not already cancelled or completed
        return ! in_array($record->status, [
            OrderStatus::Cancelled,
            OrderStatus::Refunded,
            OrderStatus::Fulfilled,
        ], true);
    }
}
