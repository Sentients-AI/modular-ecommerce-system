<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Queue Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @php $queueStats = $this->getQueueStats(); @endphp
            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold {{ $queueStats['pending'] > 100 ? 'text-warning-600' : 'text-success-600' }}">
                        {{ number_format($queueStats['pending']) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Pending Jobs</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold {{ $queueStats['failed'] > 0 ? 'text-danger-600' : 'text-success-600' }}">
                        {{ number_format($queueStats['failed']) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Failed Jobs</div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="text-center">
                    <div class="text-3xl font-bold text-info-600">
                        {{ $this->getCacheStats()['driver'] }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Cache Driver</div>
                </div>
            </x-filament::section>
        </div>

        {{-- Database Stats --}}
        <x-filament::section>
            <x-slot name="heading">
                Database Statistics
            </x-slot>
            <x-slot name="description">
                Record counts across main tables
            </x-slot>

            @php $dbStats = $this->getDatabaseStats(); @endphp
            @if(isset($dbStats['error']))
                <p class="text-sm text-danger-600">Error: {{ $dbStats['error'] }}</p>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @foreach($dbStats as $table => $count)
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="text-xl font-semibold">{{ number_format($count) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', ucfirst($table)) }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Failed Jobs --}}
        <x-filament::section>
            <x-slot name="heading">
                Failed Jobs
            </x-slot>
            <x-slot name="description">
                Recent job failures requiring attention
            </x-slot>

            @if($this->getFailedJobs()->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No failed jobs.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left">ID</th>
                                <th class="px-4 py-2 text-left">Queue</th>
                                <th class="px-4 py-2 text-left">Job</th>
                                <th class="px-4 py-2 text-left">Failed At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getFailedJobs() as $job)
                                <tr>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $job->uuid ?? $job->id }}</td>
                                    <td class="px-4 py-2">
                                        <x-filament::badge color="gray">
                                            {{ $job->queue }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="px-4 py-2">
                                        @php
                                            $payload = json_decode($job->payload, true);
                                            $displayName = $payload['displayName'] ?? 'Unknown';
                                        @endphp
                                        <span class="text-xs">{{ class_basename($displayName) }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-danger-600">
                                        {{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        {{-- Recent Errors --}}
        <x-filament::section>
            <x-slot name="heading">
                Recent Application Errors
            </x-slot>
            <x-slot name="description">
                Last 20 errors from application logs
            </x-slot>

            @if($this->getRecentErrors()->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No recent errors found.</p>
            @else
                <div class="space-y-4">
                    @foreach($this->getRecentErrors() as $error)
                        <div class="p-4 bg-danger-50 dark:bg-danger-900/20 rounded-lg border border-danger-200 dark:border-danger-800">
                            <div class="flex items-center gap-2 mb-2">
                                <x-filament::badge color="danger">
                                    {{ $error['level'] }}
                                </x-filament::badge>
                                <span class="text-xs text-gray-500">{{ $error['timestamp'] }}</span>
                            </div>
                            <p class="text-sm text-danger-700 dark:text-danger-300 font-mono break-all">
                                {{ \Illuminate\Support\Str::limit($error['message'], 200) }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- Idempotency Conflicts --}}
        <x-filament::section collapsed>
            <x-slot name="heading">
                Idempotency Conflicts (Last 24h)
            </x-slot>
            <x-slot name="description">
                Requests that encountered idempotency key conflicts
            </x-slot>

            @if($this->getIdempotencyConflicts()->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">No conflicts detected.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2 text-left">Key</th>
                                <th class="px-4 py-2 text-left">Created</th>
                                <th class="px-4 py-2 text-left">Result</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getIdempotencyConflicts() as $conflict)
                                <tr>
                                    <td class="px-4 py-2 font-mono text-xs">{{ \Illuminate\Support\Str::limit($conflict->key, 30) }}</td>
                                    <td class="px-4 py-2">{{ $conflict->created_at->diffForHumans() }}</td>
                                    <td class="px-4 py-2">
                                        <x-filament::badge color="warning">
                                            Conflict
                                        </x-filament::badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
