<?php

declare(strict_types=1);

namespace App\Domain\Config\DTOs;

use App\Shared\DTOs\BaseData;

final class UpdateConfigData extends BaseData
{
    public function __construct(
        public string $group,
        public string $key,
        public mixed $value,
        public ?int $updatedBy = null,
    ) {}

    /**
     * Create from request data.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromRequest(array $data): self
    {
        return new self(
            group: $data['group'],
            key: $data['key'],
            value: $data['value'],
            updatedBy: $data['updated_by'] ?? null,
        );
    }
}
