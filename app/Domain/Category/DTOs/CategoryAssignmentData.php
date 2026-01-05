<?php

declare(strict_types=1);

namespace App\Domain\Category\DTOs;

use App\Shared\DTOs\BaseData;

final class CategoryAssignmentData extends BaseData
{
    public function __construct(
        public string $productId,
        public string $categoryId,
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            productId: $data['product_id'],
            categoryId: $data['category_id'],
        );
    }
}
