<?php

declare(strict_types=1);

namespace App\Shared\Domain;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class DomainEvent
{
    use Dispatchable, SerializesModels;

    public readonly string $occurredAt;

    public function __construct()
    {
        $this->occurredAt = now()->toISOString();
    }
}
