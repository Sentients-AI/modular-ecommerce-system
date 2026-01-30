<?php

declare(strict_types=1);

namespace App\Domain\Order\Models;

use App\Domain\Order\Enums\OrderStatus;
use App\Domain\Payment\Models\PaymentIntent;
use App\Domain\Refund\Models\Refund;
use App\Domain\User\Models\User;
use App\Shared\Models\BaseModel;
use Database\Factories\OrderFactory;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Order extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'subtotal_cents',
        'tax_cents',
        'shipping_cost_cents',
        'total_cents',
        'currency',
        'refunded_amount_cents',
    ];

    /**
     * Generate a unique order number.
     */
    public static function generateOrderNumber(): string
    {
        return 'ORD-'.mb_strtoupper(mb_substr(uniqid(), -8));
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payment intent for this order.
     */
    public function paymentIntent(): HasOne
    {
        return $this->hasOne(PaymentIntent::class);
    }

    /**
     * Get the refunds for this order.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Check if order is pending.
     */
    public function isPending(): bool
    {
        return $this->status === OrderStatus::Pending;
    }

    /**
     * Check if order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === OrderStatus::Fulfilled;
    }

    /**
     * Check if order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::Cancelled;
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    public function isRefunded(): bool
    {
        return $this->status === OrderStatus::Refunded;
    }

    public function isRefundable(): bool
    {
        return $this->status->isRefundable();
    }

    public function markPartiallyRefunded(int $amountCents): void
    {
        if ($amountCents <= 0) {
            return;
        }

        if (! $this->status->isRefundable()) {
            throw new DomainException(
                "Cannot refund order in {$this->status->value} state"
            );
        }

        $this->refunded_amount_cents = ($this->refunded_amount_cents ?? 0) + $amountCents;

        if ($this->refunded_amount_cents >= $this->total_cents) {
            $this->refunded_amount_cents = $this->total_cents;
            $this->status = OrderStatus::Refunded;
        } else {
            $this->status = OrderStatus::PartiallyRefunded;
        }

        $this->save();
    }

    public function markRefunded(): void
    {
        if (! $this->status->isRefundable()) {
            throw new DomainException(
                "Cannot refund order in {$this->status->value} state"
            );
        }

        $this->refunded_amount_cents = $this->total_cents;
        $this->status = OrderStatus::Refunded;
        $this->save();
    }

    public function getRemainingRefundableAmount(): int
    {
        return $this->total_cents - ($this->refunded_amount_cents ?? 0);
    }

    public function isPartiallyRefunded(): bool
    {
        return $this->status === OrderStatus::PartiallyRefunded;
    }

    public function getRefundedAmountCents(): int
    {
        return $this->refunded_amount_cents ?? 0;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal_cents' => 'integer',
            'tax_cents' => 'integer',
            'shipping_cost_cents' => 'integer',
            'total_cents' => 'integer',
            'refunded_amount_cents' => 'integer',
            'status' => OrderStatus::class,
        ];
    }
}
