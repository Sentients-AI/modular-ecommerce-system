<?php

namespace App\Domain\Idempotency\Actions;

use App\Domain\Idempotency\Models\IdempotencyKey;

final class StoreIdempotencyResultAction
{
    public function execute(
        string $key,
        int $userId,
        string $action,
        array $payload,
        array $response
    ): void {
        IdempotencyKey::query()->create([
            'key' => $key,
            'user_id' => $userId,
            'action' => $action,
            'request_hash' => bcrypt(json_encode($payload)),
            'response' => $response,
        ]);
    }
}
