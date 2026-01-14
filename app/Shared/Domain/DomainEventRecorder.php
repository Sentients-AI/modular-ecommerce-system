<?php

declare(strict_types=1);

namespace App\Shared\Domain;

final class DomainEventRecorder
{
    public static function record(DomainEvent $event): void
    {
        DomainEventRecord::query()->create([
            'event_type' => $event::class,
            'payload' => get_object_vars($event),
            'occurred_at' => now(),
        ]);
    }
}
