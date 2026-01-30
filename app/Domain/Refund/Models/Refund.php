<?php

declare(strict_types=1);

namespace App\Domain\Refund\Models;

use App\Domain\Order\Models\Order;
use App\Domain\Refund\Enums\RefundStatus;
use App\Domain\User\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Refund extends Model
{
    protected $fillable = [
        'order_id',
        'payment_intent_id',
        'amount_cents',
        'currency',
        'status',
        'reason',
        'approved_by',
        'approved_at',
        'external_refund_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'status' => RefundStatus::class,
        'amount_cents' => 'integer',
        'currency' => 'string',
        'reason' => 'string',
        'external_refund_id' => 'string',
        'created_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approve(int $adminId): void
    {
        if ($this->status !== RefundStatus::PendingApproval) {
            throw new DomainException('Refund cannot be approved');
        }

        $this->update([
            'status' => RefundStatus::Approved,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
    }
}
