<?php

declare(strict_types=1);

namespace App\Domain\Payment\Models;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Shared\Models\BaseModel;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Payment extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'gateway_response',
        'paid_at',
        'failed_at',
    ];

    /**
     * Get the order that owns the payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending;
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === PaymentStatus::Capture;
    }

    /**
     * Check if payment has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === PaymentStatus::Failed;
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted(string $transactionId, ?array $gatewayResponse = null): self
    {
        $this->update([
            'status' => PaymentStatus::Capture,
            'transaction_id' => $transactionId,
            'gateway_response' => $gatewayResponse,
            'paid_at' => now(),
        ]);

        return $this;
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(?array $gatewayResponse = null): self
    {
        $this->update([
            'status' => PaymentStatus::Failed,
            'gateway_response' => $gatewayResponse,
            'failed_at' => now(),
        ]);

        return $this;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PaymentFactory
    {
        return PaymentFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'status' => PaymentStatus::class,
        ];
    }
}
