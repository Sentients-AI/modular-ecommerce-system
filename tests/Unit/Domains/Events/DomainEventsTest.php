<?php

declare(strict_types=1);

use App\Domain\Payment\Events\PaymentSucceeded;
use App\Domain\Refund\Events\RefundApproved;
use App\Domain\Refund\Events\RefundFailed;
use App\Domain\Refund\Events\RefundRequested;
use App\Domain\Refund\Events\RefundSucceeded;
use Tests\TestCase;

uses(TestCase::class);

describe('PaymentSucceeded Event', function () {
    it('has correct properties', function () {
        $event = new PaymentSucceeded(
            paymentIntentId: 1,
            orderId: 2,
            amountCents: 5000,
            currency: 'USD',
        );

        expect($event->paymentIntentId)->toBe(1);
        expect($event->orderId)->toBe(2);
        expect($event->amountCents)->toBe(5000);
        expect($event->currency)->toBe('USD');
    });
});

describe('RefundRequested Event', function () {
    it('has correct properties', function () {
        $event = new RefundRequested(
            refundId: 1,
            orderId: 2,
            amountCents: 3000,
            currency: 'USD',
            reason: 'Customer request',
        );

        expect($event->refundId)->toBe(1);
        expect($event->orderId)->toBe(2);
        expect($event->amountCents)->toBe(3000);
        expect($event->currency)->toBe('USD');
        expect($event->reason)->toBe('Customer request');
    });
});

describe('RefundApproved Event', function () {
    it('has correct properties', function () {
        $event = new RefundApproved(
            refundId: 1,
            orderId: 2,
            amountCents: 3000,
            currency: 'USD',
            approvedBy: 5,
        );

        expect($event->refundId)->toBe(1);
        expect($event->orderId)->toBe(2);
        expect($event->amountCents)->toBe(3000);
        expect($event->currency)->toBe('USD');
        expect($event->approvedBy)->toBe(5);
    });
});

describe('RefundSucceeded Event', function () {
    it('has correct properties', function () {
        $event = new RefundSucceeded(
            refundId: 1,
            orderId: 2,
            amountCents: 3000,
            currency: 'USD',
        );

        expect($event->refundId)->toBe(1);
        expect($event->orderId)->toBe(2);
        expect($event->amountCents)->toBe(3000);
        expect($event->currency)->toBe('USD');
    });
});

describe('RefundFailed Event', function () {
    it('has correct properties', function () {
        $event = new RefundFailed(
            refundId: 1,
            orderId: 2,
            reason: 'Insufficient funds',
        );

        expect($event->refundId)->toBe(1);
        expect($event->orderId)->toBe(2);
        expect($event->reason)->toBe('Insufficient funds');
    });
});
