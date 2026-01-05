<?php

declare(strict_types=1);

namespace App\Domain\Product\Actions;

use App\Domain\Inventory\Actions\AdjustStock;
use App\Domain\Inventory\DTOs\AdjustStockData;
use App\Domain\Product\DTOs\UpdateStockData;
use App\Domain\Product\Models\Product;

final class UpdateProductStock
{
    public function __construct(
        private readonly AdjustStock $adjustStock,
    ) {}

    /**
     * Execute the action to update product stock.
     */
    public function execute(UpdateStockData $data): Product
    {
        $product = Product::query()->findOrFail($data->productId);

        $adjustStockData = new AdjustStockData(
            productId: $data->productId,
            quantityChange: $data->quantity,
            reason: $data->reason ?? 'Manual stock update',
        );

        $this->adjustStock->execute($adjustStockData);

        return $product->fresh();
    }
}
