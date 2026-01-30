<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Actions;

use App\Domain\Inventory\DTOs\ReconcileStockData;
use App\Domain\Inventory\Enums\StockMovementType;
use App\Domain\Inventory\Models\Stock;
use App\Domain\Inventory\Models\StockMovement;
use App\Shared\Domain\AuditLog;
use Illuminate\Support\Facades\DB;

final class ReconcileStockAction
{
    public function execute(ReconcileStockData $data): Stock
    {
        return DB::transaction(function () use ($data) {
            $stock = Stock::query()
                ->where('product_id', $data->productId)
                ->lockForUpdate()
                ->firstOrFail();

            $previousQuantity = $stock->quantity_available;
            $difference = $data->actualCount - $previousQuantity;

            if ($difference === 0) {
                return $stock;
            }

            $stock->update(['quantity_available' => $data->actualCount]);

            StockMovement::query()->create([
                'stock_id' => $stock->id,
                'product_id' => $data->productId,
                'type' => StockMovementType::Reconciliation,
                'quantity' => abs($difference),
                'reason' => $data->reason,
                'user_id' => $data->userId,
            ]);

            AuditLog::log(
                action: 'stock_reconciled',
                targetType: 'stock',
                targetId: $stock->id,
                payload: [
                    'product_id' => $data->productId,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $data->actualCount,
                    'difference' => $difference,
                    'reason' => $data->reason,
                ],
            );

            return $stock->fresh();
        });
    }
}
