<?php

declare(strict_types=1);

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\OrderItem;
use App\Domain\Payment\Models\PaymentIntent;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('generates unique order numbers', function () {
    $orderNumber1 = Order::generateOrderNumber();
    $orderNumber2 = Order::generateOrderNumber();

    expect($orderNumber1)->toStartWith('ORD-');
    expect($orderNumber2)->toStartWith('ORD-');
    expect($orderNumber1)->not->toBe($orderNumber2);
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    expect($order->user)->toBeInstanceOf(User::class);
    expect($order->user->id)->toBe($user->id);
});

it('has many order items', function () {
    $order = Order::factory()->create();
    OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

    expect($order->items)->toHaveCount(3);
    expect($order->items->first())->toBeInstanceOf(OrderItem::class);
});

it('has one payment intent', function () {
    $order = Order::factory()->create();
    PaymentIntent::factory()->create(['order_id' => $order->id]);

    expect($order->paymentIntent)->toBeInstanceOf(PaymentIntent::class);
});

it('correctly identifies pending status', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    expect($order->isPending())->toBeTrue();
    expect($order->isPaid())->toBeFalse();
});

it('correctly identifies paid status', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Paid]);

    expect($order->isPaid())->toBeTrue();
    expect($order->isPending())->toBeFalse();
});

it('correctly identifies refunded status', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Refunded]);

    expect($order->isRefunded())->toBeTrue();
    expect($order->isPaid())->toBeFalse();
});

it('correctly identifies cancelled status', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Cancelled]);

    expect($order->isCancelled())->toBeTrue();
    expect($order->isPending())->toBeFalse();
});

it('correctly identifies completed status', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Fulfilled]);

    expect($order->isCompleted())->toBeTrue();
});

it('determines if order is refundable', function () {
    $paidOrder = Order::factory()->create(['status' => OrderStatus::Paid]);
    $pendingOrder = Order::factory()->create(['status' => OrderStatus::Pending]);
    $refundedOrder = Order::factory()->create(['status' => OrderStatus::Refunded]);

    expect($paidOrder->isRefundable())->toBeTrue();
    expect($pendingOrder->isRefundable())->toBeFalse();
    expect($refundedOrder->isRefundable())->toBeFalse();
});

it('marks order as partially refunded', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Paid,
        'total_cents' => 10000,
        'refunded_amount_cents' => 0,
    ]);

    $order->markPartiallyRefunded(3000);

    expect($order->fresh()->status)->toBe(OrderStatus::PartiallyRefunded);
    expect($order->fresh()->refunded_amount_cents)->toBe(3000);
});

it('marks order as fully refunded when refund exceeds total', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Paid,
        'total_cents' => 10000,
        'refunded_amount_cents' => 8000,
    ]);

    $order->markPartiallyRefunded(5000);

    expect($order->fresh()->status)->toBe(OrderStatus::Refunded);
    expect($order->fresh()->refunded_amount_cents)->toBe(10000);
});

it('marks order as refunded', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Paid,
        'total_cents' => 10000,
        'refunded_amount_cents' => 0,
    ]);

    $order->markRefunded();

    expect($order->fresh()->status)->toBe(OrderStatus::Refunded);
    expect($order->fresh()->refunded_amount_cents)->toBe(10000);
});

it('correctly identifies partially refunded status', function () {
    $partialOrder = Order::factory()->create(['status' => OrderStatus::PartiallyRefunded]);
    $paidOrder = Order::factory()->create(['status' => OrderStatus::Paid]);

    expect($partialOrder->isPartiallyRefunded())->toBeTrue();
    expect($paidOrder->isPartiallyRefunded())->toBeFalse();
});

it('gets refunded amount cents with default', function () {
    $order = Order::factory()->create(['refunded_amount_cents' => 0]);

    expect($order->getRefundedAmountCents())->toBe(0);
});

it('casts attributes correctly', function () {
    $order = Order::factory()->create([
        'subtotal_cents' => 5000,
        'tax_cents' => 500,
        'total_cents' => 5500,
        'status' => OrderStatus::Pending,
    ]);

    expect($order->subtotal_cents)->toBeInt();
    expect($order->tax_cents)->toBeInt();
    expect($order->total_cents)->toBeInt();
    expect($order->status)->toBeInstanceOf(OrderStatus::class);
});

it('does not mark partially refunded with zero or negative amount', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::Paid,
        'total_cents' => 10000,
        'refunded_amount_cents' => 0,
    ]);

    $order->markPartiallyRefunded(0);
    expect($order->fresh()->refunded_amount_cents)->toBe(0);

    $order->markPartiallyRefunded(-100);
    expect($order->fresh()->refunded_amount_cents)->toBe(0);
});
