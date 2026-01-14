<?php

declare(strict_types=1);

use App\Domain\Order\Models\Order;
use App\Domain\Projections\Actions\UpdateOrderFinancialsOnRefund;
use App\Domain\Projections\Models\OrderFinancialProjection;
use App\Domain\Refund\Events\RefundSucceeded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('UpdateOrderFinancialsOnRefund', function () {
    it('updates refunded amount on projection', function () {
        $order = Order::factory()->create([
            'total_cents' => 10000,
        ]);

        OrderFinancialProjection::create([
            'order_id' => $order->id,
            'total_amount' => 10000,
            'paid_amount' => 10000,
            'refunded_amount' => 0,
            'refund_status' => null,
        ]);

        $event = new RefundSucceeded(
            refundId: 1,
            orderId: $order->id,
            amountCents: 3000,
            currency: 'USD',
        );

        $action = app(UpdateOrderFinancialsOnRefund::class);
        $action->handle($event);

        $projection = OrderFinancialProjection::find($order->id);
        expect($projection->refunded_amount)->toBe(3000);
        expect($projection->refund_status)->toBe('partially_refunded');
    });

    it('marks as fully refunded when refund equals total', function () {
        $order = Order::factory()->create([
            'total_cents' => 10000,
        ]);

        OrderFinancialProjection::create([
            'order_id' => $order->id,
            'total_amount' => 10000,
            'paid_amount' => 10000,
            'refunded_amount' => 7000,
            'refund_status' => 'partially_refunded',
        ]);

        $event = new RefundSucceeded(
            refundId: 1,
            orderId: $order->id,
            amountCents: 3000,
            currency: 'USD',
        );

        $action = app(UpdateOrderFinancialsOnRefund::class);
        $action->handle($event);

        $projection = OrderFinancialProjection::find($order->id);
        expect($projection->refunded_amount)->toBe(10000);
        expect($projection->refund_status)->toBe('refunded');
    });
});
