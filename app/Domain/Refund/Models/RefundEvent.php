<?php

declare(strict_types=1);

namespace App\Domain\Refund\Models;

use App\Shared\Models\BaseModel;

final class RefundEvent extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'refund_id',
        'type',
        'payload',
        'occurred_at',
    ];

    protected array $dates = [
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];
}
