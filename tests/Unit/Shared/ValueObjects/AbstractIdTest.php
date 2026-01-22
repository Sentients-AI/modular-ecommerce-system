<?php

declare(strict_types=1);

use App\Domain\Order\ValueObjects\OrderId;
use App\Domain\User\ValueObjects\UserId;

it('creates from positive integer', function () {
    $id = OrderId::fromInt(123);

    expect($id->toInt())->toBe(123);
    expect((string) $id)->toBe('123');
    expect($id->jsonSerialize())->toBe(123);
});

it('throws exception for zero', function () {
    OrderId::fromInt(0);
})->throws(InvalidArgumentException::class);

it('throws exception for negative', function () {
    OrderId::fromInt(-5);
})->throws(InvalidArgumentException::class);

it('correctly compares equality for same type and value', function () {
    $id1 = OrderId::fromInt(123);
    $id2 = OrderId::fromInt(123);

    expect($id1->equals($id2))->toBeTrue();
});

it('returns false for same type but different value', function () {
    $id1 = OrderId::fromInt(123);
    $id2 = OrderId::fromInt(456);

    expect($id1->equals($id2))->toBeFalse();
});

it('returns false for different type with same value', function () {
    $orderId = OrderId::fromInt(123);
    $userId = UserId::fromInt(123);

    expect($orderId->equals($userId))->toBeFalse();
});

it('handles nullable int correctly', function () {
    $id = OrderId::fromNullableInt(123);
    expect($id)->toBeInstanceOf(OrderId::class);
    expect($id->toInt())->toBe(123);
});

it('returns null for null input', function () {
    $id = OrderId::fromNullableInt(null);
    expect($id)->toBeNull();
});
