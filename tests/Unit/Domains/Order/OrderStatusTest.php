<?php

declare(strict_types=1);

use App\Domain\Order\Enums\OrderStatus;
use Tests\TestCase;

uses(TestCase::class);

it('allows pending to transition to paid', function () {
    expect(OrderStatus::Pending->canTransitionTo(OrderStatus::Paid))->toBeTrue();
});

it('allows pending to transition to cancelled', function () {
    expect(OrderStatus::Pending->canTransitionTo(OrderStatus::Cancelled))->toBeTrue();
});

it('allows paid to transition to shipped', function () {
    expect(OrderStatus::Paid->canTransitionTo(OrderStatus::Shipped))->toBeTrue();
});

it('allows paid to transition to cancelled', function () {
    expect(OrderStatus::Paid->canTransitionTo(OrderStatus::Cancelled))->toBeTrue();
});

it('allows shipped to transition to fulfilled', function () {
    expect(OrderStatus::Shipped->canTransitionTo(OrderStatus::Fulfilled))->toBeTrue();
});

it('does not allow shipped to transition to paid', function () {
    expect(OrderStatus::Shipped->canTransitionTo(OrderStatus::Paid))->toBeFalse();
});

it('does not allow fulfilled to transition to any status', function () {
    expect(OrderStatus::Fulfilled->canTransitionTo(OrderStatus::Pending))->toBeFalse();
    expect(OrderStatus::Fulfilled->canTransitionTo(OrderStatus::Paid))->toBeFalse();
    expect(OrderStatus::Fulfilled->canTransitionTo(OrderStatus::Shipped))->toBeFalse();
});

it('has correct string values', function () {
    expect(OrderStatus::Pending->value)->toBe('pending');
    expect(OrderStatus::Paid->value)->toBe('paid');
    expect(OrderStatus::Fulfilled->value)->toBe('fulfilled');
    expect(OrderStatus::Cancelled->value)->toBe('cancelled');
    expect(OrderStatus::Refunded->value)->toBe('refunded');
    expect(OrderStatus::PartiallyRefunded->value)->toBe('partially_refunded');
});
