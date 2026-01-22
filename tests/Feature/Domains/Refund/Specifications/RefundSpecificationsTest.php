<?php

declare(strict_types=1);

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Models\PaymentIntent;
use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Models\Refund;
use App\Domain\Refund\Specifications\RefundAmountIsValid;
use App\Domain\Refund\Specifications\RefundCanBeApproved;
use App\Domain\Refund\Specifications\RefundCanBeProcessed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createRefundWithStatus(RefundStatus $status): Refund
{
    $order = Order::factory()->create(['status' => OrderStatus::Paid]);
    $paymentIntent = PaymentIntent::factory()->create(['order_id' => $order->id]);

    return Refund::create([
        'order_id' => $order->id,
        'payment_intent_id' => $paymentIntent->id,
        'amount_cents' => 5000,
        'currency' => 'USD',
        'status' => $status,
        'reason' => 'Test refund',
    ]);
}

it('RefundCanBeApproved is satisfied for requested refunds', function () {
    $refund = createRefundWithStatus(RefundStatus::Requested);

    $spec = new RefundCanBeApproved;

    expect($spec->isSatisfiedBy($refund))->toBeTrue();
});

it('RefundCanBeApproved is satisfied for pending approval refunds', function () {
    $refund = createRefundWithStatus(RefundStatus::PendingApproval);

    $spec = new RefundCanBeApproved;

    expect($spec->isSatisfiedBy($refund))->toBeTrue();
});

it('RefundCanBeApproved is not satisfied for already approved refunds', function () {
    $refund = createRefundWithStatus(RefundStatus::Approved);

    $spec = new RefundCanBeApproved;

    expect($spec->isSatisfiedBy($refund))->toBeFalse();
    expect($spec->getFailureReason())->toContain('approved');
});

it('RefundCanBeProcessed is satisfied for approved refunds', function () {
    $refund = createRefundWithStatus(RefundStatus::Approved);

    $spec = new RefundCanBeProcessed;

    expect($spec->isSatisfiedBy($refund))->toBeTrue();
});

it('RefundCanBeProcessed is not satisfied for non-approved refunds', function () {
    $refund = createRefundWithStatus(RefundStatus::Requested);

    $spec = new RefundCanBeProcessed;

    expect($spec->isSatisfiedBy($refund))->toBeFalse();
    expect($spec->getFailureReason())->toContain('must be approved first');
});

it('RefundAmountIsValid validates positive amount', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Paid,
        'total_cents' => 10000,
        'refunded_amount_cents' => 0,
    ]);

    $spec = new RefundAmountIsValid(5000);

    expect($spec->isSatisfiedBy($order))->toBeTrue();
});

it('RefundAmountIsValid rejects zero amount', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Paid,
        'total_cents' => 10000,
        'refunded_amount_cents' => 0,
    ]);

    $spec = new RefundAmountIsValid(0);

    expect($spec->isSatisfiedBy($order))->toBeFalse();
    expect($spec->getFailureReason())->toContain('must be positive');
});

it('RefundAmountIsValid rejects amount exceeding refundable', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Paid,
        'total_cents' => 10000,
        'refunded_amount_cents' => 8000,
    ]);

    $spec = new RefundAmountIsValid(5000);

    expect($spec->isSatisfiedBy($order))->toBeFalse();
    expect($spec->getFailureReason())->toContain('exceeds');
});

it('composes OrderIsRefundable AND RefundAmountIsValid', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Paid,
        'total_cents' => 10000,
        'refunded_amount_cents' => 0,
    ]);

    $spec = (new \App\Domain\Order\Specifications\OrderIsRefundable)
        ->and(new RefundAmountIsValid(5000));

    expect($spec->isSatisfiedBy($order))->toBeTrue();
});
