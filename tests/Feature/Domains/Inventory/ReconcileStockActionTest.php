<?php

declare(strict_types=1);

use App\Domain\Inventory\Actions\ReconcileStockAction;
use App\Domain\Inventory\DTOs\ReconcileStockData;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Product\Models\Product;
use App\Domain\User\Models\User;
use App\Shared\Domain\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('ReconcileStockAction', function () {
    it('reconciles stock to actual count', function () {
        $product = Product::factory()->create();
        $stock = Stock::create([
            'product_id' => $product->id,
            'quantity_available' => 50,
            'quantity_reserved' => 5,
        ]);

        $action = app(ReconcileStockAction::class);
        $result = $action->execute(new ReconcileStockData(
            productId: (string) $product->id,
            actualCount: 45,
            reason: 'Physical count adjustment',
        ));

        expect($result->quantity_available)->toBe(45);
    });

    it('creates stock movement record', function () {
        $product = Product::factory()->create();
        $stock = Stock::create([
            'product_id' => $product->id,
            'quantity_available' => 50,
            'quantity_reserved' => 0,
        ]);

        app(ReconcileStockAction::class)->execute(new ReconcileStockData(
            productId: (string) $product->id,
            actualCount: 40,
            reason: 'Shrinkage',
        ));

        $movement = StockMovement::query()
            ->where('stock_id', $stock->id)
            ->where('type', StockMovementType::Reconciliation)
            ->first();

        expect($movement)->not->toBeNull();
        expect($movement->quantity)->toBe(10);
        expect($movement->reason)->toBe('Shrinkage');
    });

    it('creates audit log', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $stock = Stock::create([
            'product_id' => $product->id,
            'quantity_available' => 100,
            'quantity_reserved' => 0,
        ]);

        $this->actingAs($user);
        app(ReconcileStockAction::class)->execute(new ReconcileStockData(
            productId: (string) $product->id,
            actualCount: 95,
            reason: 'Audit adjustment',
            userId: (string) $user->id,
        ));

        $auditLog = AuditLog::query()
            ->where('action', 'stock_reconciled')
            ->where('target_id', $stock->id)
            ->first();

        expect($auditLog)->not->toBeNull();
        expect($auditLog->payload['previous_quantity'])->toBe(100);
        expect($auditLog->payload['new_quantity'])->toBe(95);
        expect($auditLog->payload['difference'])->toBe(-5);
    });

    it('does nothing when actual count matches', function () {
        $product = Product::factory()->create();
        $stock = Stock::create([
            'product_id' => $product->id,
            'quantity_available' => 50,
            'quantity_reserved' => 0,
        ]);

        $action = app(ReconcileStockAction::class);
        $result = $action->execute(new ReconcileStockData(
            productId: (string) $product->id,
            actualCount: 50,
            reason: 'No change needed',
        ));

        expect($result->quantity_available)->toBe(50);

        $movementCount = StockMovement::query()
            ->where('stock_id', $stock->id)
            ->where('type', StockMovementType::Reconciliation)
            ->count();

        expect($movementCount)->toBe(0);
    });

    it('can increase stock via reconciliation', function () {
        $product = Product::factory()->create();
        $stock = Stock::create([
            'product_id' => $product->id,
            'quantity_available' => 40,
            'quantity_reserved' => 0,
        ]);

        $action = app(ReconcileStockAction::class);
        $result = $action->execute(new ReconcileStockData(
            productId: (string) $product->id,
            actualCount: 50,
            reason: 'Found extra inventory',
        ));

        expect($result->quantity_available)->toBe(50);
    });
});
