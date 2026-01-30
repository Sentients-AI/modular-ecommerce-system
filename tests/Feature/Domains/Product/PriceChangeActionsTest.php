<?php

declare(strict_types=1);

use App\Domain\Product\Actions\ChangePriceAction;
use App\Domain\Product\Actions\SchedulePriceChangeAction;
use App\Domain\Product\DTOs\ChangePriceData;
use App\Domain\Product\Models\PriceHistory;
use App\Domain\Product\Models\Product;
use App\Domain\User\Models\User;
use App\Shared\Domain\AuditLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('ChangePriceAction', function () {
    it('changes product price immediately', function () {
        $product = Product::factory()->create([
            'price_cents' => 1000,
            'sale_price' => null,
        ]);

        $action = app(ChangePriceAction::class);
        $result = $action->execute(new ChangePriceData(
            productId: $product->id,
            newPriceCents: 1500,
            newSalePrice: null,
            reason: 'Price adjustment',
        ));

        expect((int) $result->price_cents)->toBe(1500);
    });

    it('creates price history record', function () {
        $product = Product::factory()->create([
            'price_cents' => 1000,
        ]);

        app(ChangePriceAction::class)->execute(new ChangePriceData(
            productId: $product->id,
            newPriceCents: 1500,
            newSalePrice: null,
            reason: 'Test reason',
        ));

        $history = PriceHistory::query()
            ->where('product_id', $product->id)
            ->first();

        expect($history)->not->toBeNull();
        expect($history->old_price_cents)->toBe(1000);
        expect($history->new_price_cents)->toBe(1500);
        expect($history->reason)->toBe('Test reason');
    });

    it('creates audit log', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price_cents' => 1000,
        ]);

        $this->actingAs($user);
        app(ChangePriceAction::class)->execute(new ChangePriceData(
            productId: $product->id,
            newPriceCents: 1500,
            newSalePrice: null,
            reason: 'Audit test',
            changedBy: $user->id,
        ));

        $auditLog = AuditLog::query()
            ->where('action', 'price_changed')
            ->where('target_id', $product->id)
            ->first();

        expect($auditLog)->not->toBeNull();
    });

    it('can change sale price', function () {
        $product = Product::factory()->create([
            'price_cents' => 1000,
            'sale_price' => null,
        ]);

        $action = app(ChangePriceAction::class);
        $result = $action->execute(new ChangePriceData(
            productId: $product->id,
            newPriceCents: 1000,
            newSalePrice: 800,
            reason: 'Sale promotion',
        ));

        expect((int) $result->sale_price)->toBe(800);
    });
});

describe('SchedulePriceChangeAction', function () {
    it('schedules a future price change', function () {
        $product = Product::factory()->create([
            'price_cents' => 1000,
        ]);

        $futureDate = Carbon::now()->addDay();

        $action = app(SchedulePriceChangeAction::class);
        $result = $action->execute(new ChangePriceData(
            productId: $product->id,
            newPriceCents: 1500,
            newSalePrice: null,
            reason: 'Scheduled increase',
            effectiveAt: $futureDate,
        ));

        expect($result)->toBeInstanceOf(PriceHistory::class);
        expect($result->isScheduled())->toBeTrue();

        $product->refresh();
        expect((int) $product->price_cents)->toBe(1000);
    });

    it('throws exception for past effective date', function () {
        $product = Product::factory()->create([
            'price_cents' => 1000,
        ]);

        $action = app(SchedulePriceChangeAction::class);
        $action->execute(new ChangePriceData(
            productId: $product->id,
            newPriceCents: 1500,
            newSalePrice: null,
            reason: 'Should fail',
            effectiveAt: Carbon::now()->subDay(),
        ));
    })->throws(DomainException::class);

    it('creates audit log for scheduled change', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user);
        app(SchedulePriceChangeAction::class)->execute(new ChangePriceData(
            productId: $product->id,
            newPriceCents: 1500,
            newSalePrice: null,
            reason: 'Scheduled audit',
            effectiveAt: Carbon::now()->addWeek(),
            changedBy: $user->id,
        ));

        $auditLog = AuditLog::query()
            ->where('action', 'price_change_scheduled')
            ->first();

        expect($auditLog)->not->toBeNull();
    });
});

describe('PriceHistory model', function () {
    it('identifies scheduled price changes', function () {
        $product = Product::factory()->create();

        $scheduled = PriceHistory::create([
            'product_id' => $product->id,
            'old_price_cents' => 1000,
            'new_price_cents' => 1500,
            'effective_at' => Carbon::now()->addDay(),
            'reason' => 'Future',
            'created_at' => now(),
        ]);

        $past = PriceHistory::create([
            'product_id' => $product->id,
            'old_price_cents' => 800,
            'new_price_cents' => 1000,
            'effective_at' => Carbon::now()->subDay(),
            'reason' => 'Past',
            'created_at' => now(),
        ]);

        expect($scheduled->isScheduled())->toBeTrue();
        expect($past->isScheduled())->toBeFalse();
    });

    it('identifies active price changes', function () {
        $product = Product::factory()->create();

        $active = PriceHistory::create([
            'product_id' => $product->id,
            'old_price_cents' => 1000,
            'new_price_cents' => 1500,
            'effective_at' => Carbon::now()->subHour(),
            'expires_at' => Carbon::now()->addDay(),
            'reason' => 'Active',
            'created_at' => now(),
        ]);

        expect($active->isActive())->toBeTrue();
    });
});
