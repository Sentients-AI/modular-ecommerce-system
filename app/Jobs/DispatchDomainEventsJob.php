<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Shared\Domain\DomainEventRecord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class DispatchDomainEventsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DomainEventRecord::query()->whereNull('processed_at')
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->each(function ($record) {
                $eventClass = $record->event_type;

                event(new $eventClass(...$record->payload));

                $record->update(['processed_at' => now()]);
            });
    }
}
