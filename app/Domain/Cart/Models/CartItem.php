<?php

declare(strict_types=1);

namespace App\Domain\Cart\Models;

use App\Domain\Product\Models\Product;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CartItem extends BaseModel
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'price_cents_snapshot',
        'tax_cents_snapshot',
        'quantity',
    ];

    /**
     * Get the cart that owns the item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the price.
     */
    public function getPriceAttribute(): int
    {
        return $this->price_cents_snapshot;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Database\Factories\CartItemFactory
    {
        return \Database\Factories\CartItemFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_cents_snapshot' => 'integer',
            'tax_cents_snapshot' => 'integer',
            'quantity' => 'integer',
        ];
    }
}
