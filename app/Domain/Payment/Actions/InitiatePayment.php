<?php

declare(strict_types=1);

namespace App\Domain\Payment\Actions;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\DTOs\PaymentData;
use App\Domain\Payment\Models\Payment;
use Illuminate\Support\Facades\DB;

final class InitiatePayment
{
    /**
     * Execute the action to initiate a payment.
     */
    public function execute(PaymentData $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $order = Order::query()->findOrFail($data->orderId);

            // Create payment record
            $payment = Payment::query()->create([
                'order_id' => $data->orderId,
                'payment_method' => $data->paymentMethod,
                'payment_gateway' => $data->paymentGateway,
                'amount' => $data->amount,
                'currency' => $data->currency,
                'status' => 'pending',
            ]);

            // Here you would integrate with actual payment gateway
            // For now, we just return the payment record
            // Example:
            // $gatewayResponse = $this->paymentGateway->charge($payment);
            // $payment->update(['transaction_id' => $gatewayResponse['id']]);

            return $payment;
        });
    }
}
