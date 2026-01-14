<?php

declare(strict_types=1);

use App\Domain\Payment\Enums\PaymentStatus;
use Tests\TestCase;

uses(TestCase::class);

it('identifies terminal statuses', function () {
    expect(PaymentStatus::Succeeded->isTerminal())->toBeTrue();
    expect(PaymentStatus::Failed->isTerminal())->toBeTrue();
    expect(PaymentStatus::Cancelled->isTerminal())->toBeTrue();
});

it('identifies non-terminal statuses', function () {
    expect(PaymentStatus::RequiresPayment->isTerminal())->toBeFalse();
    expect(PaymentStatus::Processing->isTerminal())->toBeFalse();
});

it('determines if can be confirmed', function () {
    expect(PaymentStatus::Processing->canBeConfirmed())->toBeTrue();
    expect(PaymentStatus::RequiresPayment->canBeConfirmed())->toBeFalse();
    expect(PaymentStatus::Succeeded->canBeConfirmed())->toBeFalse();
});

it('has correct string values', function () {
    expect(PaymentStatus::RequiresPayment->value)->toBe('requires_payment');
    expect(PaymentStatus::Processing->value)->toBe('processing');
    expect(PaymentStatus::Failed->value)->toBe('failed');
    expect(PaymentStatus::Cancelled->value)->toBe('cancelled');
    expect(PaymentStatus::Succeeded->value)->toBe('succeeded');
});
