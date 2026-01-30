<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Domain\Category\Actions\CreateCategoryAction;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function afterCreate(): void
    {
        // The category was created via Filament's default mechanism.
        // We audit the creation via the CreateCategoryAction pattern for consistency.
        // However, since Filament already created it, we just ensure audit logging.
        \App\Shared\Domain\AuditLog::log(
            action: 'category_created',
            targetType: 'category',
            targetId: $this->record->id,
            payload: [
                'name' => $this->record->name,
                'slug' => $this->record->slug,
                'parent_id' => $this->record->parent_id,
            ],
        );
    }
}
