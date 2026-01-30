<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Shared\Domain\AuditLog;
use DomainException;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    private array $oldData = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function () {
                    if ($this->record->hasChildren()) {
                        throw new DomainException('Cannot delete a category that has child categories.');
                    }
                    if ($this->record->products()->exists()) {
                        throw new DomainException('Cannot delete a category that has products.');
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldData = $this->record->only(['name', 'slug', 'parent_id', 'is_active', 'sort_order']);

        return $data;
    }

    protected function afterSave(): void
    {
        AuditLog::log(
            action: 'category_updated',
            targetType: 'category',
            targetId: $this->record->id,
            payload: [
                'old' => $this->oldData,
                'new' => $this->record->only(['name', 'slug', 'parent_id', 'is_active', 'sort_order']),
            ],
        );
    }
}
