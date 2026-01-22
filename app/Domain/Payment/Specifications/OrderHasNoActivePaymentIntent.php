<?php

declare(strict_types=1);

namespace App\Domain\Payment\Specifications;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntent;
use App\Shared\Specifications\AbstractSpecification;

/**
 * @extends AbstractSpecification<Order>
 */
final class OrderHasNoActivePaymentIntent extends AbstractSpecification
{
    public function isSatisfiedBy(mixed $candidate): bool
    {
        if (! $candidate instanceof Order) {
            $this->setFailureReason('Candidate must be an Order instance');

            return false;
        }

        $existingActive = PaymentIntent::query()
            ->where('order_id', $candidate->id)
            ->whereIn('status', [PaymentStatus::RequiresPayment, PaymentStatus::Processing])
            ->exists();

        if ($existingActive) {
            $this->setFailureReason(
                'Order already has an active payment intent'
            );

            return false;
        }

        return true;
    }
}
