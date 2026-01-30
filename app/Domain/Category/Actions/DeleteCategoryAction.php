<?php

declare(strict_types=1);

namespace App\Domain\Category\Actions;

use App\Domain\Category\Models\Category;
use App\Shared\Domain\AuditLog;
use DomainException;

final class DeleteCategoryAction
{
    public function execute(Category $category): void
    {
        if ($category->children()->exists()) {
            throw new DomainException('Cannot delete a category that has child categories.');
        }

        if ($category->products()->exists()) {
            throw new DomainException('Cannot delete a category that has products.');
        }

        $categoryData = $category->only(['id', 'name', 'slug']);

        $category->delete();

        AuditLog::log(
            action: 'category_deleted',
            targetType: 'category',
            targetId: $categoryData['id'],
            payload: $categoryData,
        );
    }
}
