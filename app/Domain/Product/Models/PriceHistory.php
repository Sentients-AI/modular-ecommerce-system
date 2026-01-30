<?php

declare(strict_types=1);

namespace App\Domain\Product\Models;

use App\Domain\User\Models\User;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PriceHistory extends BaseModel
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'old_price_cents',
        'new_price_cents',
        'old_sale_price',
        'new_sale_price',
        'effective_at',
        'expires_at',
        'changed_by',
        'reason',
        'created_at',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function isScheduled(): bool
    {
        return $this->effective_at->isFuture();
    }

    public function isActive(): bool
    {
        $now = now();

        return $this->effective_at->lte($now)
            && ($this->expires_at === null || $this->expires_at->gt($now));
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_price_cents' => 'integer',
            'new_price_cents' => 'integer',
            'old_sale_price' => 'integer',
            'new_sale_price' => 'integer',
            'effective_at' => 'datetime',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
