<?php

declare(strict_types=1);

namespace App\Domain\Category\Actions;

use App\Domain\Category\DTOs\CategoryData;
use App\Domain\Category\Models\Category;
use App\Shared\Domain\AuditLog;
use DomainException;
use Illuminate\Support\Str;

final class UpdateCategoryAction
{
    public function execute(Category $category, CategoryData $data): Category
    {
        if ($data->parentId === $category->id) {
            throw new DomainException('A category cannot be its own parent.');
        }

        $oldData = $category->only(['name', 'slug', 'parent_id', 'is_active', 'sort_order']);

        $category->update([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'description' => $data->description,
            'parent_id' => $data->parentId,
            'is_active' => $data->isActive,
            'sort_order' => $data->sortOrder,
        ]);

        AuditLog::log(
            action: 'category_updated',
            targetType: 'category',
            targetId: $category->id,
            payload: [
                'old' => $oldData,
                'new' => $category->only(['name', 'slug', 'parent_id', 'is_active', 'sort_order']),
            ],
        );

        return $category->fresh();
    }
}
