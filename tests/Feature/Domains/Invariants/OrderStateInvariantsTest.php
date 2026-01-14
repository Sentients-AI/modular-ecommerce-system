<?php

declare(strict_types=1);

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Support\OrderStateGuard;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntent;
use App\Domain\Refund\Actions\ApproveRefundAction;
use App\Domain\Refund\Actions\InitiateRefundAction;
use App\Domain\Refund\Actions\ProcessRefundAction;
use App\Domain\Refund\Actions\RequestRefundAction;
use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Models\Refund;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('INV-ORD-003: Order status must never regress', function () {

    it('prevents Paid from transitioning back to Pending', function () {
        expect(OrderStatus::Paid->canTransitionTo(OrderStatus::Pending))->toBeFalse();
    });

    it('prevents Shipped from transitioning back to Paid', function () {
        expect(OrderStatus::Shipped->canTransitionTo(OrderStatus::Paid))->toBeFalse();
    });

    it('prevents Fulfilled from transitioning to any backward state', function () {
        expect(OrderStatus::Fulfilled->canTransitionTo(OrderStatus::Pending))->toBeFalse();
        expect(OrderStatus::Fulfilled->canTransitionTo(OrderStatus::Paid))->toBeFalse();
        expect(OrderStatus::Fulfilled->canTransitionTo(OrderStatus::Shipped))->toBeFalse();
    });

    it('prevents terminal states from changing', function () {
        expect(OrderStatus::Cancelled->isTerminal())->toBeTrue();
        expect(OrderStatus::Failed->isTerminal())->toBeTrue();
        expect(OrderStatus::Refunded->isTerminal())->toBeTrue();

        expect(OrderStatus::Cancelled->canTransitionTo(OrderStatus::Pending))->toBeFalse();
        expect(OrderStatus::Failed->canTransitionTo(OrderStatus::Pending))->toBeFalse();
        expect(OrderStatus::Refunded->canTransitionTo(OrderStatus::Pending))->toBeFalse();
    });
});

describe('INV-ORD-004: Cancelled/Refunded order must never transition to Shipped/Delivered', function () {

    it('prevents cancelled order from being shipped', function () {
        expect(OrderStatus::Cancelled->canTransitionTo(OrderStatus::Shipped))->toBeFalse();
    });

    it('prevents refunded order from being shipped', function () {
        expect(OrderStatus::Refunded->canTransitionTo(OrderStatus::Shipped))->toBeFalse();
    });

    it('throws exception when trying to ship cancelled order via guard', function () {
        OrderStateGuard::canShip(OrderStatus::Cancelled);
    })->throws(DomainException::class);

});

describe('INV-PAY-001: Payment intent must never be confirmed twice', function () {

    it('recognizes terminal payment states', function () {
        expect(PaymentStatus::Succeeded->isTerminal())->toBeTrue();
        expect(PaymentStatus::Failed->isTerminal())->toBeTrue();
        expect(PaymentStatus::Cancelled->isTerminal())->toBeTrue();
        expect(PaymentStatus::Processing->isTerminal())->toBeFalse();
    });

    it('only allows confirmation from Processing state', function () {
        expect(PaymentStatus::Processing->canBeConfirmed())->toBeTrue();
        expect(PaymentStatus::Succeeded->canBeConfirmed())->toBeFalse();
        expect(PaymentStatus::Failed->canBeConfirmed())->toBeFalse();
    });
});

describe('INV-REF-001: Refund must never be requested for unpaid order', function () {

    it('throws exception when requesting refund for pending order', function () {
        $order = Order::factory()->create([
            'status' => OrderStatus::Pending,
            'total_cents' => 10000,
        ]);

        $action = app(RequestRefundAction::class);
        $action->execute($order, 5000, 'Customer request');
    })->throws(DomainException::class, 'Refund can only be requested for refundable orders.');

    it('throws exception when initiating refund for cancelled order', function () {
        $order = Order::factory()->create([
            'status' => OrderStatus::Cancelled,
            'total_cents' => 10000,
        ]);

        $action = app(InitiateRefundAction::class);
        $action->execute($order, 5000, 'Customer request');
    })->throws(DomainException::class, 'Order is not refundable');
});

describe('INV-REF-002: Total refunded amount must never exceed order total', function () {

    it('throws exception when refund amount exceeds order total', function () {
        $order = Order::factory()->create([
            'status' => OrderStatus::Paid,
            'total_cents' => 10000,
        ]);
        PaymentIntent::factory()->create([
            'order_id' => $order->id,
            'provider_reference' => 'pi_test_123',
        ]);

        $action = app(RequestRefundAction::class);
        $action->execute($order, 15000, 'Customer request');
    })->throws(DomainException::class);

    it('throws exception when refund exceeds remaining refundable amount', function () {
        $order = Order::factory()->create([
            'status' => OrderStatus::PartiallyRefunded,
            'total_cents' => 10000,
            'refunded_amount_cents' => 7000,
        ]);
        PaymentIntent::factory()->create([
            'order_id' => $order->id,
            'provider_reference' => 'pi_test_123',
        ]);

        $action = app(RequestRefundAction::class);
        $action->execute($order, 5000, 'Customer request');
    })->throws(DomainException::class);

    it('clamps refunded amount to order total in markPartiallyRefunded', function () {
        $order = Order::factory()->create([
            'status' => OrderStatus::Paid,
            'total_cents' => 10000,
            'refunded_amount_cents' => 0,
        ]);

        $order->markPartiallyRefunded(15000);

        expect($order->refunded_amount_cents)->toBe(10000);
        expect($order->status)->toBe(OrderStatus::Refunded);
    });
});

describe('INV-REF-003: Refund must never be processed without approval', function () {

    it('throws exception when processing unapproved refund', function () {
        $order = Order::factory()->create();

        $refund = Refund::create([
            'order_id' => $order->id,
            'payment_intent_id' => 'pi_test_123',
            'amount_cents' => 5000,
            'currency' => 'USD',
            'status' => RefundStatus::Requested,
            'reason' => 'Customer request',
        ]);

        $mockGateway = Mockery::mock(App\Domain\Payment\Contracts\PaymentGatewayService::class);
        $this->app->instance(App\Domain\Payment\Contracts\PaymentGatewayService::class, $mockGateway);

        $action = app(ProcessRefundAction::class);
        $action->execute($refund);
    })->throws(DomainException::class);

    it('throws exception when processing pending approval refund', function () {
        $order = Order::factory()->create();

        $refund = Refund::create([
            'order_id' => $order->id,
            'payment_intent_id' => 'pi_test_123',
            'amount_cents' => 5000,
            'currency' => 'USD',
            'status' => RefundStatus::PendingApproval,
            'reason' => 'Customer request',
        ]);

        $mockGateway = Mockery::mock(App\Domain\Payment\Contracts\PaymentGatewayService::class);
        $this->app->instance(App\Domain\Payment\Contracts\PaymentGatewayService::class, $mockGateway);

        $action = app(ProcessRefundAction::class);
        $action->execute($refund);
    })->throws(DomainException::class);
});

describe('INV-REF-004: Terminal refund must never be re-approved', function () {

    it('prevents approving succeeded refund', function () {
        expect(RefundStatus::Succeeded->canBeApproved())->toBeFalse();
    });

    it('prevents approving failed refund', function () {
        expect(RefundStatus::Failed->canBeApproved())->toBeFalse();
    });

    it('throws exception when approving succeeded refund', function () {
        $order = Order::factory()->create();
        $admin = User::factory()->create();

        $refund = Refund::create([
            'order_id' => $order->id,
            'payment_intent_id' => 'pi_test_123',
            'amount_cents' => 5000,
            'currency' => 'USD',
            'status' => RefundStatus::Succeeded,
            'reason' => 'Customer request',
        ]);

        $action = app(ApproveRefundAction::class);
        $action->execute($refund, $admin);
    })->throws(DomainException::class);
});

describe('INV-REF-005: Fully refunded order must never accept additional refunds', function () {

    it('marks fully refunded order as not refundable', function () {
        $order = Order::factory()->create([
            'status' => OrderStatus::Refunded,
            'total_cents' => 10000,
        ]);

        expect($order->isRefundable())->toBeFalse();
    });

    it('throws exception when marking non-refundable order as refunded', function () {
        $order = Order::factory()->create([
            'status' => OrderStatus::Cancelled,
            'total_cents' => 10000,
        ]);

        $order->markRefunded();
    })->throws(DomainException::class);
});

describe('Refund state machine transitions', function () {

    it('allows valid transitions from Requested to Approved', function () {
        expect(RefundStatus::Requested->canTransitionTo(RefundStatus::Approved))->toBeTrue();
    });

    it('allows valid transitions from Approved to Processing', function () {
        expect(RefundStatus::Approved->canTransitionTo(RefundStatus::Processing))->toBeTrue();
    });

    it('allows valid transitions from Processing to Succeeded', function () {
        expect(RefundStatus::Processing->canTransitionTo(RefundStatus::Succeeded))->toBeTrue();
    });

    it('prevents invalid transitions from Succeeded', function () {
        expect(RefundStatus::Succeeded->canTransitionTo(RefundStatus::Approved))->toBeFalse();
        expect(RefundStatus::Succeeded->canTransitionTo(RefundStatus::Processing))->toBeFalse();
        expect(RefundStatus::Succeeded->isTerminal())->toBeTrue();
    });
});

describe('Order valid state transitions', function () {

    it('allows Pending to transition to Paid', function () {
        expect(OrderStatus::Pending->canTransitionTo(OrderStatus::Paid))->toBeTrue();
    });

    it('allows Paid to transition to Shipped', function () {
        expect(OrderStatus::Paid->canTransitionTo(OrderStatus::Shipped))->toBeTrue();
    });

    it('allows Paid to transition to PartiallyRefunded', function () {
        expect(OrderStatus::Paid->canTransitionTo(OrderStatus::PartiallyRefunded))->toBeTrue();
    });

    it('allows PartiallyRefunded to transition to Refunded', function () {
        expect(OrderStatus::PartiallyRefunded->canTransitionTo(OrderStatus::Refunded))->toBeTrue();
    });

    it('allows Fulfilled to be refunded', function () {
        expect(OrderStatus::Fulfilled->canTransitionTo(OrderStatus::Refunded))->toBeTrue();
        expect(OrderStatus::Fulfilled->isRefundable())->toBeTrue();
    });
});
