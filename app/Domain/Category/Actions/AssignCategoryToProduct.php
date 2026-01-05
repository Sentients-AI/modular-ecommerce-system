<?php

declare(strict_types=1);

namespace App\Domain\Category\Actions;

use App\Domain\Category\DTOs\CategoryAssignmentData;
use App\Domain\Product\Models\Product;

final class AssignCategoryToProduct
{
    /**
     * Execute the action to assign a category to a product.
     */
    public function execute(CategoryAssignmentData $data): Product
    {
        $product = Product::query()->findOrFail($data->productId);

        $product->update(['category_id' => $data->categoryId]);

        return $product->fresh();
    }
}
