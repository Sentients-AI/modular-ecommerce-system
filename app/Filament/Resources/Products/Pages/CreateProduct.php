<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Shared\Domain\AuditLog;
use Filament\Resources\Pages\CreateRecord;

final class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        AuditLog::log(
            action: 'product_created',
            targetType: 'product',
            targetId: $this->record->id,
            payload: [
                'sku' => $this->record->sku,
                'name' => $this->record->name,
                'price_cents' => $this->record->price_cents,
                'category_id' => $this->record->category_id,
            ],
        );
    }
}
