<?php

declare(strict_types=1);

use App\Domain\Refund\Enums\RefundStatus;
use Tests\TestCase;

uses(TestCase::class);

it('identifies terminal statuses', function () {
    expect(RefundStatus::Succeeded->isTerminal())->toBeTrue();
    expect(RefundStatus::Failed->isTerminal())->toBeTrue();
    expect(RefundStatus::Rejected->isTerminal())->toBeTrue();
    expect(RefundStatus::Cancelled->isTerminal())->toBeTrue();
});

it('identifies non-terminal statuses', function () {
    expect(RefundStatus::Requested->isTerminal())->toBeFalse();
    expect(RefundStatus::Approved->isTerminal())->toBeFalse();
    expect(RefundStatus::Processing->isTerminal())->toBeFalse();
    expect(RefundStatus::PendingApproval->isTerminal())->toBeFalse();
});

it('has correct string values', function () {
    expect(RefundStatus::Requested->value)->toBe('requested');
    expect(RefundStatus::Approved->value)->toBe('approved');
    expect(RefundStatus::Processing->value)->toBe('processing');
    expect(RefundStatus::Succeeded->value)->toBe('succeeded');
    expect(RefundStatus::Failed->value)->toBe('failed');
    expect(RefundStatus::Rejected->value)->toBe('rejected');
    expect(RefundStatus::Cancelled->value)->toBe('cancelled');
    expect(RefundStatus::PendingApproval->value)->toBe('pending_approval');
});
