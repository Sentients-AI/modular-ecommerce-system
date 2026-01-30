<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Shared\Domain\AuditLog;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    private array $oldData = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldData = $this->record->only([
            'name', 'slug', 'description', 'short_description',
            'category_id', 'is_active', 'is_featured', 'meta_title', 'meta_description',
        ]);

        return $data;
    }

    protected function afterSave(): void
    {
        AuditLog::log(
            action: 'product_updated',
            targetType: 'product',
            targetId: $this->record->id,
            payload: [
                'sku' => $this->record->sku,
                'old' => $this->oldData,
                'new' => $this->record->only([
                    'name', 'slug', 'description', 'short_description',
                    'category_id', 'is_active', 'is_featured', 'meta_title', 'meta_description',
                ]),
            ],
        );
    }
}
