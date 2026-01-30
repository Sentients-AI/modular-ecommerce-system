<?php

declare(strict_types=1);

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Payment\Models\PaymentIntent;
use App\Domain\Role\Models\Role;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('loads the payment view page successfully', function () {
    $role = Role::factory()->create(['name' => 'admin']);
    $user = User::factory()->create(['role_id' => $role->id]);
    $order = Order::factory()->create([
        'status' => OrderStatus::AwaitingPayment,
    ]);
    $payment = PaymentIntent::factory()->create([
        'order_id' => $order->id,
        'status' => PaymentStatus::Succeeded,
    ]);

    $response = $this->actingAs($user)
        ->get("/control-plane/payments/{$payment->id}");

    $response->assertStatus(200);
});

it('loads the payments list page successfully', function () {
    $role = Role::factory()->create(['name' => 'admin']);
    $user = User::factory()->create(['role_id' => $role->id]);

    $response = $this->actingAs($user)
        ->get('/control-plane/payments');

    $response->assertStatus(200);
});
