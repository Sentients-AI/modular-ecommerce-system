<?php

declare(strict_types=1);

namespace App\Filament\Resources\Refunds\Pages;

use App\Domain\Refund\Actions\ApproveRefundAction;
use App\Domain\Refund\Actions\ProcessRefundAction;
use App\Domain\Refund\Actions\RejectRefundAction;
use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Models\Refund;
use App\Filament\Resources\Refunds\RefundResource;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

final class ViewRefund extends ViewRecord
{
    protected static string $resource = RefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve_refund')
                ->label('Approve Refund')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Refund')
                ->modalDescription('Are you sure you want to approve this refund? Once approved, it can be executed.')
                ->visible(fn (Refund $record): bool => $this->canApproveRefund($record) && auth()->user()->can('approve', $record))
                ->action(function (Refund $record): void {
                    try {
                        app(ApproveRefundAction::class)->execute($record, auth()->user());

                        Notification::make()
                            ->title('Refund Approved')
                            ->body("Refund #{$record->id} has been approved.")
                            ->success()
                            ->send();

                        $this->refreshFormData(['status', 'approved_by', 'approved_at']);
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Approval Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('reject_refund')
                ->label('Reject Refund')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reject Refund')
                ->modalDescription('Please provide a reason for rejecting this refund.')
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Rejection Reason')
                        ->required()
                        ->maxLength(500),
                ])
                ->visible(fn (Refund $record): bool => $this->canRejectRefund($record) && auth()->user()->can('reject', $record))
                ->action(function (Refund $record, array $data): void {
                    try {
                        app(RejectRefundAction::class)->execute(
                            $record,
                            auth()->user(),
                            $data['rejection_reason']
                        );

                        Notification::make()
                            ->title('Refund Rejected')
                            ->body("Refund #{$record->id} has been rejected.")
                            ->warning()
                            ->send();

                        $this->refreshFormData(['status', 'reason']);
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Rejection Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('execute_refund')
                ->label('Execute Refund')
                ->icon('heroicon-o-banknotes')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Execute Refund')
                ->modalDescription('Are you sure you want to execute this refund? This will process the payment refund through the payment gateway.')
                ->visible(fn (Refund $record): bool => $this->canExecuteRefund($record) && auth()->user()->can('execute', $record))
                ->action(function (Refund $record): void {
                    try {
                        app(ProcessRefundAction::class)->execute($record);

                        Notification::make()
                            ->title('Refund Executed')
                            ->body("Refund #{$record->id} has been successfully processed.")
                            ->success()
                            ->send();

                        $this->refreshFormData(['status', 'external_refund_id']);
                    } catch (Exception $e) {
                        Notification::make()
                            ->title('Execution Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    private function canApproveRefund(Refund $record): bool
    {
        return $record->status->canBeApproved();
    }

    private function canRejectRefund(Refund $record): bool
    {
        return $record->status->canBeApproved();
    }

    private function canExecuteRefund(Refund $record): bool
    {
        return $record->status === RefundStatus::Approved;
    }
}
