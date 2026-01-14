<?php

declare(strict_types=1);

namespace App\Domain\Cart\Models;

use App\Domain\User\Models\User;
use App\Shared\Models\BaseModel;
use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property $user_id
 * @@property-read $session_id
 */
final class Cart extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the total number of items in the cart.
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get the subtotal of the cart.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function (CartItem $item) {
            return $item->quantity * (float) $item->price;
        });
    }

    /**
     * Check if cart is empty.
     */
    public function isEmpty(): bool
    {
        return $this->items()->count() === 0;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CartFactory
    {
        return CartFactory::new();
    }
}
