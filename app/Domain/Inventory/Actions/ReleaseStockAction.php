<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\DTOs\ReserveStockData;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockMovement;
use Illuminate\Support\Facades\DB;

final class ReleaseStockAction
{
    /**
     * Execute the action to release reserved stock.
     */
    public function execute(ReserveStockData $data): Stock
    {
        return DB::transaction(function () use ($data) {
            $stock = Stock::query()
                ->where('product_id', $data->productId)
                ->lockForUpdate()
                ->firstOrFail();

            $releaseQuantity = min($data->quantity, $stock->quantity_reserved);

            $stock->decrement('quantity_reserved', $releaseQuantity);

            StockMovement::query()->create([
                'stock_id' => $stock->id,
                'product_id' => $data->productId,
                'type' => 'release',
                'quantity' => $releaseQuantity,
                'reference_type' => $data->referenceType,
                'reference_id' => $data->referenceId,
                'reason' => 'Stock released',
            ]);

            return $stock->fresh();
        });
    }
}
