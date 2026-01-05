<?php

declare(strict_types=1);

namespace App\Domain\Order\Models;

use App\Domain\Product\Models\Product;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

final class OrderItem extends BaseModel
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
        'order_id',
        'product_id',
        'price_cents_snapshot',
        'tax_cents_snapshot',
        'quantity',
    ];

    /**
     * Get the order that owns the item.
     */
    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product.
     */
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): \Database\Factories\OrderItemFactory
    {
        return \Database\Factories\OrderItemFactory::new();
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
