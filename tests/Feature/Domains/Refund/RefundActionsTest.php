<?php

declare(strict_types=1);

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Contracts\PaymentGatewayService;
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

describe('RequestRefundAction', function () {
    it('creates a refund for paid order', function () {
        $order = Order::factory()->create([
            'status' => OrderStatus::Paid,
            'total_cents' => 10000,
        ]);
        PaymentIntent::factory()->create([
            'order_id' => $order->id,
            'provider_reference' => 'pi_test_123',
        ]);

        $action = app(RequestRefundAction::class);
        $refund = $action->execute($order, 5000, 'Customer request');

        expect($refund)->toBeInstanceOf(Refund::class);
        expect($refund->order_id)->toBe($order->id);
        expect($refund->amount_cents)->toBe(5000);
        expect($refund->status)->toBe(RefundStatus::Requested);
        expect($refund->reason)->toBe('Customer request');
    });

    it('throws exception for unpaid order', function () {
        $order = Order::factory()->create([
            'status' => OrderStatus::Pending,
        ]);

        $action = app(RequestRefundAction::class);
        $action->execute($order, 5000, 'Customer request');
    })->throws(DomainException::class, 'Refund can only be requested for refundable orders.');
});

describe('InitiateRefundAction', function () {
    it('initiates a refund for refundable order', function () {
        $order = Order::factory()->create([
            'status' => OrderStatus::Paid,
            'total_cents' => 10000,
        ]);
        PaymentIntent::factory()->create([
            'order_id' => $order->id,
            'provider_reference' => 'pi_test_123',
        ]);

        $action = app(InitiateRefundAction::class);
        $refund = $action->execute($order, 5000, 'Damaged item');

        expect($refund)->toBeInstanceOf(Refund::class);
        expect($refund->status)->toBe(RefundStatus::PendingApproval);
        expect($refund->amount_cents)->toBe(5000);
    });

    it('throws exception for non-refundable order', function () {
        $order = Order::factory()->create([
            'status' => OrderStatus::Pending,
        ]);

        $action = app(InitiateRefundAction::class);
        $action->execute($order, 5000, 'Damaged item');
    })->throws(DomainException::class, 'Order is not refundable');
});

describe('ApproveRefundAction', function () {
    it('approves a requested refund', function () {
        $order = Order::factory()->create();
        $admin = User::factory()->create();

        $refund = Refund::create([
            'order_id' => $order->id,
            'payment_intent_id' => 'pi_test_123',
            'amount_cents' => 5000,
            'currency' => 'USD',
            'status' => RefundStatus::Requested,
            'reason' => 'Customer request',
        ]);

        $action = app(ApproveRefundAction::class);
        $result = $action->execute($refund, $admin);

        expect($result->status)->toBe(RefundStatus::Approved);
        expect($result->approved_by)->toBe($admin->id);
        expect($result->approved_at)->not->toBeNull();
    });

    it('approves a pending approval refund', function () {
        $order = Order::factory()->create();
        $admin = User::factory()->create();

        $refund = Refund::create([
            'order_id' => $order->id,
            'payment_intent_id' => 'pi_test_123',
            'amount_cents' => 5000,
            'currency' => 'USD',
            'status' => RefundStatus::PendingApproval,
            'reason' => 'Customer request',
        ]);

        $action = app(ApproveRefundAction::class);
        $result = $action->execute($refund, $admin);

        expect($result->status)->toBe(RefundStatus::Approved);
    });

    it('throws exception for already processed refund', function () {
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

describe('ProcessRefundAction', function () {
    it('processes an approved refund', function () {
        $order = Order::factory()->create();

        $refund = Refund::create([
            'order_id' => $order->id,
            'payment_intent_id' => 'pi_test_123',
            'amount_cents' => 5000,
            'currency' => 'USD',
            'status' => RefundStatus::Approved,
            'reason' => 'Customer request',
        ]);

        $mockGateway = Mockery::mock(PaymentGatewayService::class);
        $mockGateway->shouldReceive('refund')
            ->once()
            ->with('pi_test_123', 5000);

        $this->app->instance(PaymentGatewayService::class, $mockGateway);

        $action = app(ProcessRefundAction::class);
        $result = $action->execute($refund);

        expect($result->status)->toBe(RefundStatus::Succeeded);
    });

    it('marks refund as failed on gateway error', function () {
        $order = Order::factory()->create();

        $refund = Refund::create([
            'order_id' => $order->id,
            'payment_intent_id' => 'pi_test_123',
            'amount_cents' => 5000,
            'currency' => 'USD',
            'status' => RefundStatus::Approved,
            'reason' => 'Customer request',
        ]);

        $mockGateway = Mockery::mock(PaymentGatewayService::class);
        $mockGateway->shouldReceive('refund')
            ->once()
            ->andThrow(new Exception('Gateway error'));

        $this->app->instance(PaymentGatewayService::class, $mockGateway);

        $action = app(ProcessRefundAction::class);

        try {
            $action->execute($refund);
        } catch (Exception $e) {
            expect($refund->fresh()->status)->toBe(RefundStatus::Failed);
        }
    });

    it('throws exception for non-approved refund', function () {
        $order = Order::factory()->create();

        $refund = Refund::create([
            'order_id' => $order->id,
            'payment_intent_id' => 'pi_test_123',
            'amount_cents' => 5000,
            'currency' => 'USD',
            'status' => RefundStatus::Requested,
            'reason' => 'Customer request',
        ]);

        $mockGateway = Mockery::mock(PaymentGatewayService::class);
        $this->app->instance(PaymentGatewayService::class, $mockGateway);

        $action = app(ProcessRefundAction::class);
        $action->execute($refund);
    })->throws(DomainException::class, 'Cannot process refund in requested state. Refund must be approved first.');
});
