<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use Illuminate\Database\Eloquent\Model;

final class DomainEventRecord extends Model
{
    protected $table = 'domain_events';

    protected $fillable = [
        'event_type',
        'payload',
        'occurred_at',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
