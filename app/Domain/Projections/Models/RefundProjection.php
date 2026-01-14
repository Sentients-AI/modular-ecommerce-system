<?php

declare(strict_types=1);

namespace App\Domain\Projections\Models;

use Illuminate\Database\Eloquent\Model;

final class RefundProjection extends Model
{
    protected $fillable = [
        'refund_id',
        'order_id',
        'amount_cents',
        'status',
        'approved_at',
        'succeeded_at',
    ];

    public function refundId(): int
    {
        return $this->attributes['refund_id'];
    }
}
