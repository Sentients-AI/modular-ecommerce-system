<?php

declare(strict_types=1);

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Models\Payment;

final class GetPaymentStatus
{
    /**
     * Execute the action to get payment status.
     */
    public function execute(string $paymentId): Payment
    {
        $payment = Payment::query()
            ->with('order')
            ->findOrFail($paymentId);

        // Here you could also check with the payment gateway
        // to get the latest status if needed
        // Example:
        // if ($payment->isPending()) {
        //     $gatewayStatus = $this->paymentGateway->getStatus($payment->transaction_id);
        //     if ($gatewayStatus['status'] === 'completed') {
        //         $payment->markAsCompleted($payment->transaction_id, $gatewayStatus);
        //     }
        // }

        return $payment;
    }
}
