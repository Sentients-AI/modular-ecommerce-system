<?php

declare(strict_types=1);

namespace App\Domain\Product\DTOs;

use App\Shared\DTOs\BaseData;

final class ProductData extends BaseData
{
    /**
     * @param  array<string>|null  $images
     */
    public function __construct(
        public string $name,
        public string $slug,
        public string $description,
        public ?string $shortDescription,
        public string $price,
        public ?string $salePrice,
        public string $sku,
        public string $categoryId,
        public bool $isActive = true,
        public bool $isFeatured = false,
        public ?array $images = null,
        public ?string $metaTitle = null,
        public ?string $metaDescription = null,
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'],
            shortDescription: $data['short_description'] ?? null,
            price: $data['price'],
            salePrice: $data['sale_price'] ?? null,
            sku: $data['sku'],
            categoryId: $data['category_id'],
            isActive: $data['is_active'] ?? true,
            isFeatured: $data['is_featured'] ?? false,
            images: $data['images'] ?? null,
            metaTitle: $data['meta_title'] ?? null,
            metaDescription: $data['meta_description'] ?? null,
        );
    }
}
