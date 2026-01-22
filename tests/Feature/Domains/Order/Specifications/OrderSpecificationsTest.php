<?php

declare(strict_types=1);

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Specifications\OrderCanBeCancelled;
use App\Domain\Order\Specifications\OrderCanTransitionToStatus;
use App\Domain\Order\Specifications\OrderIsRefundable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('OrderIsRefundable is satisfied for paid orders', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Paid]);

    $spec = new OrderIsRefundable;

    expect($spec->isSatisfiedBy($order))->toBeTrue();
});

it('OrderIsRefundable is not satisfied for pending orders', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    $spec = new OrderIsRefundable;

    expect($spec->isSatisfiedBy($order))->toBeFalse();
    expect($spec->getFailureReason())->toContain('pending');
});

it('OrderIsRefundable is satisfied for partially refunded orders', function () {
    $order = Order::factory()->create(['status' => OrderStatus::PartiallyRefunded]);

    $spec = new OrderIsRefundable;

    expect($spec->isSatisfiedBy($order))->toBeTrue();
});

it('OrderCanTransitionToStatus validates transitions', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    $canTransitionToPaid = new OrderCanTransitionToStatus(OrderStatus::Paid);
    expect($canTransitionToPaid->isSatisfiedBy($order))->toBeTrue();

    $canTransitionToShipped = new OrderCanTransitionToStatus(OrderStatus::Shipped);
    expect($canTransitionToShipped->isSatisfiedBy($order))->toBeFalse();
});

it('OrderCanBeCancelled validates cancellation rules', function () {
    $pendingOrder = Order::factory()->create(['status' => OrderStatus::Pending]);

    $spec = new OrderCanBeCancelled;

    expect($spec->isSatisfiedBy($pendingOrder))->toBeTrue();
});

it('OrderCanBeCancelled is not satisfied for already cancelled orders', function () {
    $cancelledOrder = Order::factory()->create(['status' => OrderStatus::Cancelled]);

    $spec = new OrderCanBeCancelled;

    expect($spec->isSatisfiedBy($cancelledOrder))->toBeFalse();
    expect($spec->getFailureReason())->toContain('already cancelled');
});

it('OrderCanBeCancelled is not satisfied for fulfilled orders', function () {
    $fulfilledOrder = Order::factory()->create(['status' => OrderStatus::Fulfilled]);

    $spec = new OrderCanBeCancelled;

    expect($spec->isSatisfiedBy($fulfilledOrder))->toBeFalse();
    expect($spec->getFailureReason())->toContain('completed');
});

it('composes OrderIsRefundable AND OrderCanTransitionToStatus', function () {
    $paidOrder = Order::factory()->create(['status' => OrderStatus::Paid]);

    $spec = (new OrderIsRefundable)
        ->and(new OrderCanTransitionToStatus(OrderStatus::PartiallyRefunded));

    expect($spec->isSatisfiedBy($paidOrder))->toBeTrue();
});

it('throws DomainException when asserting unsatisfied specification', function () {
    $cancelledOrder = Order::factory()->create(['status' => OrderStatus::Cancelled]);

    $spec = new OrderIsRefundable;
    $spec->assertSatisfiedBy($cancelledOrder);
})->throws(DomainException::class);
