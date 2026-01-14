<?php

declare(strict_types=1);

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\Refund\Models\Refund;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('belongs to an order', function () {
    $order = Order::factory()->create();
    $refund = Refund::create([
        'order_id' => $order->id,
        'payment_intent_id' => 'pi_test_123',
        'amount_cents' => 5000,
        'currency' => 'USD',
        'status' => RefundStatus::Requested,
        'reason' => 'Customer request',
    ]);

    expect($refund->order)->toBeInstanceOf(Order::class);
    expect($refund->order->id)->toBe($order->id);
});

it('can be approved by admin', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Paid]);
    $admin = User::factory()->create();

    $refund = Refund::create([
        'order_id' => $order->id,
        'payment_intent_id' => 'pi_test_123',
        'amount_cents' => 5000,
        'currency' => 'USD',
        'status' => RefundStatus::PendingApproval,
        'reason' => 'Customer request',
    ]);

    $refund->approve($admin->id);

    expect($refund->fresh()->status)->toBe(RefundStatus::Approved);
    expect($refund->fresh()->approved_by)->toBe($admin->id);
    expect($refund->fresh()->approved_at)->not->toBeNull();
});

it('throws exception when approving non-pending refund', function () {
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

    $refund->approve($admin->id);
})->throws(DomainException::class, 'Refund cannot be approved');

it('casts attributes correctly', function () {
    $order = Order::factory()->create();

    $refund = Refund::create([
        'order_id' => $order->id,
        'payment_intent_id' => 'pi_test_123',
        'amount_cents' => 5000,
        'currency' => 'USD',
        'status' => RefundStatus::Requested,
        'reason' => 'Customer request',
        'approved_at' => now(),
    ]);

    expect($refund->status)->toBeInstanceOf(RefundStatus::class);
    expect($refund->amount_cents)->toBeInt();
    expect($refund->approved_at)->not->toBeNull();
});
