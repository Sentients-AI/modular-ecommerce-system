<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\DTOs\AdjustStockData;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

final class AdjustStockAction
{
    /**
     * Execute the action to adjust stock quantity.
     */
    public function execute(AdjustStockData $data): Stock
    {
        return DB::transaction(function () use ($data) {
            $stock = Stock::query()
                ->where('product_id', $data->productId)
                ->lockForUpdate()
                ->firstOrFail();

            $newQuantity = $stock->quantity + $data->quantityChange;

            if ($newQuantity < 0) {
                throw new Exception('Stock adjustment would result in negative quantity');
            }

            $stock->update(['quantity' => $newQuantity]);

            StockMovement::query()->create([
                'stock_id' => $stock->id,
                'product_id' => $data->productId,
                'type' => $data->quantityChange > 0 ? 'adjustment_in' : 'adjustment_out',
                'quantity' => abs($data->quantityChange),
                'reason' => $data->reason,
                'user_id' => $data->userId,
            ]);

            return $stock->fresh();
        });
    }
}
