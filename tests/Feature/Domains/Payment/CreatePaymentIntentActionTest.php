<?php

declare(strict_types=1);

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Actions\CreatePaymentIntentAction;
use App\Domain\Payment\Contracts\PaymentGatewayService;
use App\Domain\Payment\DTOs\CreatePaymentIntentDTO;
use App\Domain\Payment\DTOs\ProviderResponse;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('creates a payment intent', function () {
    $order = Order::factory()->create();

    $mockGateway = Mockery::mock(PaymentGatewayService::class);
    $mockGateway->shouldReceive('createIntent')
        ->once()
        ->andReturn(new ProviderResponse(
            provider: 'stripe',
            reference: 'pi_test_123',
            clientSecret: 'secret_123',
        ));

    $this->app->instance(PaymentGatewayService::class, $mockGateway);

    $action = app(CreatePaymentIntentAction::class);
    $intent = $action->execute(new CreatePaymentIntentDTO(
        orderId: $order->id,
        amount: 5000,
        currency: 'USD',
        idempotencyKey: 'test_key_123',
    ));

    expect($intent)->toBeInstanceOf(PaymentIntent::class);
    expect($intent->order_id)->toBe($order->id);
    expect($intent->amount)->toBe(5000);
    expect($intent->currency)->toBe('USD');
    expect($intent->provider)->toBe('stripe');
    expect($intent->provider_reference)->toBe('pi_test_123');
    expect($intent->status)->toBe(PaymentStatus::Processing);
});

it('returns existing intent for duplicate idempotency key', function () {
    $order = Order::factory()->create();
    $existingIntent = PaymentIntent::factory()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'duplicate_key',
        'amount' => 3000,
    ]);

    $mockGateway = Mockery::mock(PaymentGatewayService::class);
    $mockGateway->shouldNotReceive('createIntent');

    $this->app->instance(PaymentGatewayService::class, $mockGateway);

    $action = app(CreatePaymentIntentAction::class);
    $intent = $action->execute(new CreatePaymentIntentDTO(
        orderId: $order->id,
        amount: 5000,
        currency: 'USD',
        idempotencyKey: 'duplicate_key',
    ));

    expect($intent->id)->toBe($existingIntent->id);
    expect($intent->amount)->toBe(3000);
});

it('returns existing active intent for same order with different idempotency key', function () {
    $order = Order::factory()->create();
    $existingIntent = PaymentIntent::factory()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'original_key',
        'amount' => 3000,
        'status' => PaymentStatus::Processing,
    ]);

    $mockGateway = Mockery::mock(PaymentGatewayService::class);
    $mockGateway->shouldNotReceive('createIntent');

    $this->app->instance(PaymentGatewayService::class, $mockGateway);

    $action = app(CreatePaymentIntentAction::class);
    $intent = $action->execute(new CreatePaymentIntentDTO(
        orderId: $order->id,
        amount: 5000,
        currency: 'USD',
        idempotencyKey: 'different_key',
    ));

    expect($intent->id)->toBe($existingIntent->id);
    expect($intent->amount)->toBe(3000);
    expect(PaymentIntent::where('order_id', $order->id)->count())->toBe(1);
});

it('creates new intent when existing intent is in terminal state', function () {
    $order = Order::factory()->create();
    PaymentIntent::factory()->create([
        'order_id' => $order->id,
        'idempotency_key' => 'failed_key',
        'amount' => 3000,
        'status' => PaymentStatus::Failed,
    ]);

    $mockGateway = Mockery::mock(PaymentGatewayService::class);
    $mockGateway->shouldReceive('createIntent')
        ->once()
        ->andReturn(new ProviderResponse(
            provider: 'stripe',
            reference: 'pi_new_123',
            clientSecret: 'secret_new',
        ));

    $this->app->instance(PaymentGatewayService::class, $mockGateway);

    $action = app(CreatePaymentIntentAction::class);
    $intent = $action->execute(new CreatePaymentIntentDTO(
        orderId: $order->id,
        amount: 5000,
        currency: 'USD',
        idempotencyKey: 'new_key',
    ));

    expect($intent->amount)->toBe(5000);
    expect($intent->provider_reference)->toBe('pi_new_123');
    expect(PaymentIntent::where('order_id', $order->id)->count())->toBe(2);
});
