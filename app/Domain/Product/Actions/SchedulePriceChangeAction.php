<?php

declare(strict_types=1);

namespace App\Domain\Product\Actions;

use App\Domain\Product\DTOs\ChangePriceData;
use App\Domain\Product\Models\PriceHistory;
use App\Domain\Product\Models\Product;
use App\Shared\Domain\AuditLog;
use DomainException;

final class SchedulePriceChangeAction
{
    public function execute(ChangePriceData $data): PriceHistory
    {
        if ($data->effectiveAt === null || $data->effectiveAt->isPast()) {
            throw new DomainException('Scheduled price change must have a future effective date.');
        }

        $product = Product::query()->findOrFail($data->productId);

        $priceHistory = PriceHistory::query()->create([
            'product_id' => $product->id,
            'old_price_cents' => $product->price_cents,
            'new_price_cents' => $data->newPriceCents,
            'old_sale_price' => $product->sale_price,
            'new_sale_price' => $data->newSalePrice,
            'effective_at' => $data->effectiveAt,
            'expires_at' => $data->expiresAt,
            'changed_by' => $data->changedBy,
            'reason' => $data->reason,
            'created_at' => now(),
        ]);

        AuditLog::log(
            action: 'price_change_scheduled',
            targetType: 'product',
            targetId: $product->id,
            payload: [
                'sku' => $product->sku,
                'current_price_cents' => $product->price_cents,
                'scheduled_price_cents' => $data->newPriceCents,
                'effective_at' => $data->effectiveAt->toISOString(),
                'expires_at' => $data->expiresAt?->toISOString(),
                'reason' => $data->reason,
            ],
        );

        return $priceHistory;
    }
}
