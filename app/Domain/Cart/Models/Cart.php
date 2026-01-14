<?php

declare(strict_types=1);

namespace App\Domain\Cart\Models;

use App\Domain\User\Models\User;
use App\Shared\Models\BaseModel;
use Database\Factories\CartFactory;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $user_id
 * @property-read string $session_id
 * @property-read \Carbon\Carbon|null $completed_at
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
        'completed_at',
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
     * Check if cart has been completed (checkout finished).
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Mark the cart as completed after successful checkout.
     * This prevents the cart from being reused or mutated.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
        ]);

        // Delete cart items as they're now part of the order
        $this->items()->delete();
    }

    /**
     * Guard to ensure cart is not completed before allowing mutations.
     *
     * @throws DomainException
     */
    public function assertNotCompleted(): void
    {
        if ($this->isCompleted()) {
            throw new DomainException('Cannot modify a completed cart');
        }
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): CartFactory
    {
        return CartFactory::new();
    }

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }
}
