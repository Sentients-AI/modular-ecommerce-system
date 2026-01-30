<?php

declare(strict_types=1);

namespace App\Domain\Category\Actions;

use App\Domain\Category\DTOs\CategoryData;
use App\Domain\Category\Models\Category;
use App\Shared\Domain\AuditLog;
use Illuminate\Support\Str;

final class CreateCategoryAction
{
    public function execute(CategoryData $data): Category
    {
        $category = Category::query()->create([
            'name' => $data->name,
            'slug' => $data->slug ?? Str::slug($data->name),
            'description' => $data->description,
            'parent_id' => $data->parentId,
            'is_active' => $data->isActive,
            'sort_order' => $data->sortOrder,
        ]);

        AuditLog::log(
            action: 'category_created',
            targetType: 'category',
            targetId: $category->id,
            payload: [
                'name' => $category->name,
                'slug' => $category->slug,
                'parent_id' => $category->parent_id,
            ],
        );

        return $category;
    }
}
