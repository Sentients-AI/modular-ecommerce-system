<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\DTOs\ReserveStockData;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

final class ReserveStock
{
    /**
     * Execute the action to reserve stock for a product.
     *
     * @throws Exception
     */
    public function execute(ReserveStockData $data): Stock
    {
        return DB::transaction(function () use ($data) {
            $stock = Stock::query()
                ->where('product_id', $data->productId)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $stock->isAvailable($data->quantity)) {
                throw new InsufficientStockException(
                    productId: $data->productId,
                    requested: $data->quantity,
                    available: $stock->quantity_available
                );
            }

            $stock->decrement('quantity_available', $data->quantity);
            $stock->increment('quantity_reserved', $data->quantity);

            StockMovement::query()->create([
                'stock_id' => $stock->id,
                'product_id' => $data->productId,
                'type' => StockMovementType::RESERVE,
                'quantity' => $data->quantity,
                'reference_type' => StockMovementType::RESERVE,
                'reference_id' => $data->orderId,
                'reason' => 'Stock reserved',
            ]);

            return $stock->fresh();
        });
    }
}
