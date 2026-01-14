<?php

declare(strict_types=1);

use App\Domain\Inventory\Actions\ReleaseStockAction;
use App\Domain\Inventory\Actions\ReserveStockAction;
use App\Domain\Inventory\DTOs\ReserveStockData;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockMovement;
use App\Domain\Product\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('ReserveStockAction', function () {
    it('reserves stock successfully', function () {
        $product = Product::factory()->create();
        Stock::factory()->create([
            'product_id' => $product->id,
            'quantity_available' => 10,
            'quantity_reserved' => 0,
        ]);

        $action = app(ReserveStockAction::class);
        $stock = $action->execute(new ReserveStockData(
            productId: $product->id,
            quantity: 3,
            orderId: 1,
        ));

        expect($stock->quantity_available)->toBe(7);
        expect($stock->quantity_reserved)->toBe(3);
    });

    it('creates stock movement record', function () {
        $product = Product::factory()->create();
        Stock::factory()->create([
            'product_id' => $product->id,
            'quantity_available' => 10,
            'quantity_reserved' => 0,
        ]);

        $action = app(ReserveStockAction::class);
        $action->execute(new ReserveStockData(
            productId: $product->id,
            quantity: 3,
            orderId: 1,
        ));

        expect(StockMovement::count())->toBe(1);
        expect(StockMovement::first()->quantity)->toBe(3);
        expect(StockMovement::first()->reason)->toBe('Stock reserved');
    });

    it('throws exception for insufficient stock', function () {
        $product = Product::factory()->create();
        Stock::factory()->create([
            'product_id' => $product->id,
            'quantity_available' => 2,
            'quantity_reserved' => 0,
        ]);

        $action = app(ReserveStockAction::class);
        $action->execute(new ReserveStockData(
            productId: $product->id,
            quantity: 5,
            orderId: 1,
        ));
    })->throws(InsufficientStockException::class);

    it('reserves exact available quantity', function () {
        $product = Product::factory()->create();
        Stock::factory()->create([
            'product_id' => $product->id,
            'quantity_available' => 5,
            'quantity_reserved' => 0,
        ]);

        $action = app(ReserveStockAction::class);
        $stock = $action->execute(new ReserveStockData(
            productId: $product->id,
            quantity: 5,
            orderId: 1,
        ));

        expect($stock->quantity_available)->toBe(0);
        expect($stock->quantity_reserved)->toBe(5);
    });
});

describe('ReleaseStockAction', function () {
    it('releases reserved stock', function () {
        $product = Product::factory()->create();
        Stock::factory()->create([
            'product_id' => $product->id,
            'quantity_available' => 5,
            'quantity_reserved' => 3,
        ]);

        $action = app(ReleaseStockAction::class);
        $stock = $action->execute(new ReserveStockData(
            productId: $product->id,
            quantity: 2,
            referenceType: 'order',
            referenceId: 1,
        ));

        expect($stock->quantity_reserved)->toBe(1);
    });

    it('creates stock movement record on release', function () {
        $product = Product::factory()->create();
        Stock::factory()->create([
            'product_id' => $product->id,
            'quantity_available' => 5,
            'quantity_reserved' => 3,
        ]);

        $action = app(ReleaseStockAction::class);
        $action->execute(new ReserveStockData(
            productId: $product->id,
            quantity: 2,
            referenceType: 'order',
            referenceId: 1,
        ));

        expect(StockMovement::count())->toBe(1);
        expect(StockMovement::first()->type)->toBe(StockMovementType::Release);
        expect(StockMovement::first()->reason)->toBe('Stock released');
    });

    it('releases only up to reserved quantity', function () {
        $product = Product::factory()->create();
        Stock::factory()->create([
            'product_id' => $product->id,
            'quantity_available' => 5,
            'quantity_reserved' => 2,
        ]);

        $action = app(ReleaseStockAction::class);
        $stock = $action->execute(new ReserveStockData(
            productId: $product->id,
            quantity: 10,
            referenceType: 'order',
            referenceId: 1,
        ));

        expect($stock->quantity_reserved)->toBe(0);
    });
});
