<?php

namespace App\Domain\Refund\Models;

use App\Domain\Refund\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Model;

final class Refund extends Model
{
    protected $fillable = [
        'order_id',
        'payment_intent_id',
        'amount_cents',
        'currency',
        'status',
        'reason',
    ];

    protected $casts = [
        'status' => RefundStatus::class,
    ];
}
