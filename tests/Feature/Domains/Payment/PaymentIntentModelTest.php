<?php

declare(strict_types=1);

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('belongs to an order', function () {
    $order = Order::factory()->create();
    $intent = PaymentIntent::factory()->create(['order_id' => $order->id]);

    expect($intent->order)->toBeInstanceOf(Order::class);
    expect($intent->order->id)->toBe($order->id);
});

it('identifies pending status', function () {
    $intent = PaymentIntent::factory()->create([
        'status' => PaymentStatus::RequiresPayment,
    ]);

    expect($intent->isPending())->toBeTrue();
    expect($intent->isCompleted())->toBeFalse();
});

it('identifies completed status', function () {
    $intent = PaymentIntent::factory()->create([
        'status' => PaymentStatus::Succeeded,
    ]);

    expect($intent->isCompleted())->toBeTrue();
    expect($intent->isPending())->toBeFalse();
});

it('marks payment as completed', function () {
    $intent = PaymentIntent::factory()->create([
        'status' => PaymentStatus::Processing,
    ]);

    $intent->markAsCompleted('txn_123', ['response' => 'success']);

    expect($intent->fresh()->status)->toBe(PaymentStatus::Succeeded);
    expect($intent->fresh()->transaction_id)->toBe('txn_123');
    expect($intent->fresh()->gateway_response)->toBe(['response' => 'success']);
});

it('marks payment as failed', function () {
    $intent = PaymentIntent::factory()->create([
        'status' => PaymentStatus::Processing,
    ]);

    $intent->markAsFailed(['error' => 'insufficient_funds']);

    expect($intent->fresh()->status)->toBe(PaymentStatus::Failed);
    expect($intent->fresh()->gateway_response)->toBe(['error' => 'insufficient_funds']);
    expect($intent->fresh()->failed_at)->not->toBeNull();
});

it('casts attributes correctly', function () {
    $intent = PaymentIntent::factory()->create([
        'amount' => 5000,
        'attempts' => 1,
        'metadata' => ['key' => 'value'],
        'status' => PaymentStatus::Processing,
    ]);

    expect($intent->amount)->toBeInt();
    expect($intent->attempts)->toBeInt();
    expect($intent->metadata)->toBeArray();
    expect($intent->status)->toBeInstanceOf(PaymentStatus::class);
});

it('casts gateway_response as array', function () {
    $intent = PaymentIntent::factory()->create([
        'gateway_response' => ['key' => 'value'],
    ]);

    expect($intent->gateway_response)->toBeArray();
    expect($intent->gateway_response['key'])->toBe('value');
});

it('casts datetime fields correctly', function () {
    $intent = PaymentIntent::factory()->create([
        'expires_at' => now()->addHour(),
        'failed_at' => now(),
    ]);

    expect($intent->expires_at)->not->toBeNull();
    expect($intent->failed_at)->not->toBeNull();
});
