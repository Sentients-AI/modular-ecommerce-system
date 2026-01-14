<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Models;

use App\Domain\Product\Models\Product;
use App\Shared\Models\BaseModel;
use Database\Factories\StockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Stock extends BaseModel
{
    use HasFactory;

    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    public const CREATED_AT = null;

    public $timestamps = true;

    protected $attributes = [
        'quantity_reserved' => 0,
        'quantity_available' => 0,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'quantity_available',
        'quantity_reserved',
        'updated_at',
    ];

    /**
     * Get the product that owns the stock.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get stock movements for this stock record.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Check if stock is available for a given quantity.
     */
    public function isAvailable(int $quantity): bool
    {
        return ($this->quantity_available - $this->quantity_reserved) >= $quantity;
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return ($this->quantity_available - $this->quantity_reserved) > 0;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): StockFactory
    {
        return StockFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_available' => 'integer',
            'quantity_reserved' => 'integer',
        ];
    }
}
