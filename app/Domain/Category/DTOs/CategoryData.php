<?php

declare(strict_types=1);

namespace App\Domain\Category\DTOs;

use App\Shared\DTOs\BaseData;

final class CategoryData extends BaseData
{
    public function __construct(
        public string $name,
        public ?string $slug = null,
        public ?string $description = null,
        public ?int $parentId = null,
        public bool $isActive = true,
        public int $sortOrder = 0,
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
            slug: $data['slug'] ?? null,
            description: $data['description'] ?? null,
            parentId: $data['parent_id'] ?? null,
            isActive: $data['is_active'] ?? true,
            sortOrder: $data['sort_order'] ?? 0,
        );
    }
}
