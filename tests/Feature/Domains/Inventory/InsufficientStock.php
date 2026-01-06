<?php

declare(strict_types=1);

use App\Domain\Inventory\Actions\ReserveStockAction;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Product\Models\Product;

it('test order fails when stock is insufficient', function () {

    $this->expectException(InsufficientStockException::class);

    $product = Product::factory()->create();
    Stock::factory()->create([
        'product_id' => $product->id,
        'quantity' => 1,
    ]);

    $action = app(ReserveStockAction::class);
    $action->execute($product->id, 5);
});
