<?php


declare(strict_types=1);

namespace App\Domain\Payment\Models;

use App\Domain\Order\Models\Order;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PaymentIntent extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'provider ',
        'provider_reference',
        'amount',
        'currency',
        'status',
        'idempotency_key',
        'metadata',
        'expires_at',
        'attempts',
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
        return $this->status === PaymentStatus::REQUIRES_PAYMENT;
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === PaymentStatus::SUCCEEDED;
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted(string $transactionId, ?array $gatewayResponse = null): self
    {
        $this->update([
            'status' => PaymentStatus::SUCCEEDED,
            'transaction_id' => $transactionId,
            'gateway_response' => $gatewayResponse,
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
        ];
    }
}
