<?php

declare(strict_types=1);

namespace App\Domain\Product\Actions;

use App\Domain\Product\DTOs\ProductData;
use App\Domain\Product\Models\Product;
use Illuminate\Support\Facades\DB;

final class CreateProduct
{
    /**
     * Execute the action to create a new product.
     */
    public function execute(ProductData $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::query()->create([
                'name' => $data->name,
                'slug' => $data->slug,
                'description' => $data->description,
                'short_description' => $data->shortDescription,
                'price' => $data->price,
                'sale_price' => $data->salePrice,
                'sku' => $data->sku,
                'category_id' => $data->categoryId,
                'is_active' => $data->isActive,
                'is_featured' => $data->isFeatured,
                'images' => $data->images,
                'meta_title' => $data->metaTitle,
                'meta_description' => $data->metaDescription,
            ]);

            // Create initial stock record
            $product->stock()->create([
                'quantity' => 0,
                'quantity_reserved' => 0,
            ]);

            return $product;
        });
    }
}
