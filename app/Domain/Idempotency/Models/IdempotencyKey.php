<?php

declare(strict_types=1);

namespace App\Domain\Idempotency\Models;

use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class IdempotencyKey extends BaseModel
{
    use HasFactory;

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'request_fingerprint',
        'response_code',
        'response_body',
        'expires_at',
    ];

    /**
     * Check if the idempotency key has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the idempotency key is valid.
     */
    public function isValid(): bool
    {
        return ! $this->isExpired();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Database\Factories\IdempotencyKeyFactory
    {
        return \Database\Factories\IdempotencyKeyFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response_code' => 'integer',
            'response_body' => 'array',
            'expires_at' => 'datetime',
        ];
    }
}
